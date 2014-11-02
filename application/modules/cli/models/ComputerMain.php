<?php

class Cli_Model_ComputerMain extends Cli_Model_ComputerFunctions
{
    public function __construct(IWebSocketConnection $user, $playerId, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        parent::__construct($user->parameters['gameId'], $playerId, $db);

        $this->_user = $user;
        $this->_gameHandler = $gameHandler;
        $this->_l = new Coret_Model_Logger();
        $this->_mGame = new Application_Model_Game($this->_gameId, $this->_db);
        $this->_turnNumber = $this->_mGame->getTurnNumber();
    }

    public function init($army)
    {
        $this->_Computer = new Cli_Model_Army($army);
        $this->_l->log('');
        $this->_l->log($this->_playerId, 'playerId: ');
        $this->_l->log($this->_Computer->id, 'armyId: ');

        $this->_enemies = Cli_Model_Army::getAllEnemiesArmies($this->_gameId, $this->_db, $this->_playerId);

        $this->initMap();

        if (isset($this->_user->parameters['computer'][$this->_playerId][$this->_Computer->id]['path']) && $this->_user->parameters['computer'][$this->_playerId][$this->_Computer->id]['path']) {
            return $this->goByThePath();
        }

        return $this->move();
    }

    private function move()
    {
        $this->_l->logMethodName();
        $myCastleId = Application_Model_Board::isCastleAtPosition($this->_Computer->x, $this->_Computer->y, $this->_map['myCastles']);

        if ($myCastleId !== null) {
            return $this->inside($myCastleId);
        } else {
            return $this->outside();
        }
    }

    private function inside($myCastleId)
    {
        $this->_l->logMethodName();
        $this->_l->log('W ZAMKU');

        $castlePosition = $this->_map['myCastles'][$myCastleId]['position'];

        $numberOfUnits = floor($this->_turnNumber / 7);
        if ($numberOfUnits > 4) {
            $numberOfUnits = 4;
        }

        if ($numberOfUnits) {
            $garrison = Cli_Model_Army::getArmiesFromCastlePosition($castlePosition, $this->_gameId, $this->_playerId, $this->_db);
            reset($garrison);
            $armyId = Cli_Model_Army::isCastleGarrisonSufficient($numberOfUnits, $garrison);

            if ($armyId) {
                $this->_mArmyDB->fortify($armyId, 1);
                if (count($garrison) > 1) {
                    $notGarrison = array();
                    foreach ($garrison as $army) {
                        if ($armyId == $army['armyId']) {
                            continue;
                        }
                        $notGarrison[] = $army;
                    }

                    if (count($notGarrison) > 1) {
                        $this->_l->log('ŁĄCZĘ ARMIE, KTÓRE PÓJDĄ DALEJ');

                        $firstArmy = current($notGarrison);

                        $path = new Cli_Model_Path(array(0 => array(
                            'x' => $firstArmy['x'],
                            'y' => $firstArmy['y'],
                            'tt' => 'c')
                        ));
                        $secondArmy = next($notGarrison);

                        $this->_Computer = new Cli_Model_Army($secondArmy);
                        $this->_Computer->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                        return $this->endMove($secondArmy['armyId'], $path);
                    } elseif (count($notGarrison) == 1) {
                        $this->_l->log('TA ARMIA IDZIE DALEJ');
                        $this->_Computer = new Cli_Model_Army(current($notGarrison));
                    }
                } else {
                    $this->_l->log('OBSADA ZAMKU - ZOSTAŃ!');
                    $army = current($garrison);
                    $this->_Computer = new Cli_Model_Army($army);
                    return $this->endMove($armyId);
                }
            } elseif (count($garrison) > 1) {
                $this->_l->log('ŁĄCZĘ ARMIE W JEDNĄ');

                $firstArmy = current($garrison);
                $path = new Cli_Model_Path(array(0 => array(
                    'x' => $firstArmy['x'],
                    'y' => $firstArmy['y'],
                    'tt' => 'c')
                ));
                $secondArmy = next($garrison);

                $this->_Computer = new Cli_Model_Army($secondArmy);
                $this->_Computer->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                return $this->endMove($secondArmy['armyId'], $path);
            } else {
                $army = current($garrison);

                if (count($army['soldiers']) > $numberOfUnits) {
                    $this->_l->log('ARMIA W ZAMKU MA WIĘCEJ JEDNOSTEK NIŻ JEST TO WYMAGANE');

                    $h = '';
                    $s = '';
                    $counter = count($army['soldiers']) - $numberOfUnits;

                    foreach ($army['heroes'] as $hero) {
                        if ($h) {
                            $h .= ',' . $hero['heroId'];
                        } else {
                            $h = $hero['heroId'];
                        }
                    }

                    foreach ($army['soldiers'] as $soldier) {
                        $counter--;
                        if ($counter < 0) {
                            break;
                        }
                        if ($s) {
                            $s .= ',' . $soldier['soldierId'];
                        } else {
                            $s = $soldier['soldierId'];
                        }
                    }

                    $mSplitArmy = new Cli_Model_SplitArmy($army['armyId'], $s, $h, $this->_user, $this->_playerId, $this->_db, $this->_gameHandler);
                    $newArmyId = $mSplitArmy->getChildArmyId();

                    if ($army['x'] == $castlePosition['x'] && $army['y'] == $castlePosition['y']) {
                        $path = new Cli_Model_Path(array(0 => array(
                            'x' => $castlePosition['x'] + 1,
                            'y' => $castlePosition['y'] + 1,
                            'tt' => 'c')
                        ));
                    } else {
                        $path = new Cli_Model_Path(array(0 => array(
                            'x' => $castlePosition['x'],
                            'y' => $castlePosition['y'],
                            'tt' => 'c')
                        ));
                    }

                    $this->_Computer = new Cli_Model_Army(Cli_Model_Army::getArmyByArmyId($newArmyId, $this->_gameId, $this->_db));
                    $this->_Computer->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                    return $this->endMove($newArmyId, $path);
                }
                $this->_l->log('ZA MAŁA OBSADA ZAMKU - ZOSTAŃ!');

                $this->_mArmyDB->fortify($this->_Computer->id, 1);
                return $this->endMove();
            }
        }

        $enemiesHaveRange = $this->getEnemiesHaveRangeAtThisCastle($castlePosition);
        $enemiesInRange = $this->getEnemiesInRange($this->_map['fields']);
        if (!$enemiesHaveRange) {
            $this->_l->log('BRAK WROGA Z ZASIĘGIEM');

            if (!$enemiesInRange) {
                $this->_l->log('BRAK WROGA W ZASIĘGU');

                return $this->ruinBlock($this->_map['myCastles']);
            } else {
                $this->_l->log('JEST WRÓG W ZASIĘGU');

                $castles = $this->_map['hostileCastles'];

                foreach ($enemiesInRange as $e) {
                    if ($castleId = Application_Model_Board::isCastleAtPosition($e->x, $e->y, $castles)) {
                        unset($castles[$castleId]);
                    }
                    if ($this->isEnemyStronger($e, $castleId)) {
                        continue;
                    } else {
                        $enemy = $e;
                        break;
                    }
                }
                if (isset($enemy)) {
                    $this->_l->log('WRÓG JEST SŁABSZY - ATAKUJ!');

                    if ($castleId !== null) {
                        $path = $this->getPathToEnemyCastleInRange($castleId);
                        $path->castleId = $castleId;
                    } else {
                        $path = $this->getPathToEnemyInRange($enemy);
                    }
                    $fightEnemyResults = $this->fightEnemy($path);
                    return $this->endMove($this->_Computer->id, $path, $fightEnemyResults);
                }

                $this->_l->log('WRÓG JEST SILNIEJSZY - ZOSTAŃ!');
                $this->_mArmyDB->fortify($this->_Computer->id, 1);
                return $this->endMove();
            }
        } else {
            $this->_l->log('JEST WRÓG Z ZASIĘGIEM');

            if ($this->_turnNumber <= 7 && !$enemiesInRange) {
                $this->_l->log('BRAK WROGA W ZASIĘGU I TURA < 8 - ZOSTAŃ!');

                $this->_mArmyDB->fortify($this->_Computer->id, 1);
                return $this->endMove();
            } else {
                $this->_l->log('JEST WRÓG W ZASIĘGU');

                if ($this->_turnNumber <= 7 && count($enemiesHaveRange) > 1) {
                    $this->_l->log('WRÓGÓW Z ZASIĘGIEM > WRÓGÓW W ZASIĘGU - ZOSTAŃ!');

                    $this->_mArmyDB->fortify($this->_Computer->id, 1);
                    return $this->endMove();
                } else {
                    $this->_l->log('TYLKO JEDEN Z WRÓGÓW Z ZASIĘGIEM LUB TURA > 7');

                    $enemy = $this->canAttackAllEnemyHaveRange($enemiesHaveRange);
                    if (!$enemy) {
                        $this->_l->log('NIE MOGĘ ZAATAKOWAĆ WRÓGÓW Z ZASIĘGIEM - ZOSTAŃ!');

                        $this->_mArmyDB->fortify($this->_Computer->id, 1);
                        return $this->endMove();
                    } else {
                        $path = $this->isEnemyArmyInRange($enemy);
                        if (empty($path->current)) {
                            $this->_l->log('WRÓG Z ZASIĘGIEM POZA ZASIĘGIEM - IDŹ DO WROGA!');
                            return $this->savePath($path);
                        }
                        $this->_l->log('ATAKUJĘ WRÓGA Z ZASIĘGIEM - ATAKUJ!');
                        $fightEnemyResults = $this->fightEnemy($path);
                        return $this->endMove($this->_Computer->id, $path, $fightEnemyResults);
                    }
                }
            }
        }
    }

    private function outside()
    {
        $this->_l->logMethodName();
        $this->_l->log('POZA ZAMKIEM');

        $path = $this->getMyEmptyCastleInMyRange();
        if (!$path) {
            $this->_l->log('NIE MA MOJEGO PUSTEGO ZAMKU W ZASIĘGU');
            return $this->ruinBlock($this->_map['myCastles']);
        } else {
            $this->_l->log('JEST MÓJ PUSTY ZAMEK W ZASIĘGU');
            if (!$this->isMyCastleInRangeOfEnemy($path, $this->_map['fields'])) {
                $this->_l->log('WRÓG NIE MA ZASIĘGU NA MÓJ PUSTY ZAMEK');
                return $this->firstBlock();
            } else {
                $this->_l->log('WRÓG MA ZASIĘG NA MÓJ PUSTY ZAMEK - IDŹ DO ZAMKU!');
                $this->_Computer->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                return $this->endMove($this->_Computer->id, $path);
            }
        }
    }

    private function firstBlock()
    {
        $this->_l->logMethodName();
        $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId, $this->_db);

        if (!$mCastlesInGame->enemiesCastlesExist($this->_playerId, $mPlayersInGame->selectPlayerTeamExceptPlayer($this->_playerId))) {
            $this->_l->log('BRAK ZAMKÓW WROGA');
            return $this->noEnemyCastlesToAttack();
        } else {
            $this->_l->log('SĄ ZAMKI WROGA');

            $pathToNearestWeakestHostileCastle = $this->findNearestWeakestHostileCastle();

            if (isset($pathToNearestWeakestHostileCastle->castleId)) {
                $this->_l->log('JEST SŁABSZY ZAMEK WROGA: ' . $pathToNearestWeakestHostileCastle->castleId);

                if ($pathToNearestWeakestHostileCastle->in) {
                    $this->_l->log('SŁABSZY ZAMEK WROGA W ZASIĘGU - ATAKUJĘ!');
                    $fightEnemyResults = $this->fightEnemy($pathToNearestWeakestHostileCastle);
                    return $this->endMove($this->_Computer->id, $pathToNearestWeakestHostileCastle, $fightEnemyResults);
                } else {
                    $this->_l->log('SŁABSZY ZAMEK WROGA POZA ZASIĘGIEM');
                    $path = $this->getWeakerEnemyArmyInRange();
                    if (isset($path->current) && $path->current) {
                        //atakuj
                        $this->_l->log('JEST SŁABSZA ARMIA WROGA W ZASIĘGU (' . $path->armyId . ') - ATAKUJĘ!');
                        $fightEnemyResults = $this->fightEnemy($path);
                        return $this->endMove($this->_Computer->id, $path, $fightEnemyResults);
                    } else {
                        $this->_l->log('BRAK SŁABSZEJ ARMII WROGA W ZASIĘGU');
                        $enemyId = $this->getStrongerEnemyArmyInRange();
                        if ($enemyId) {
                            $this->_l->log('JEST SILNIEJSZA ARMIA WROGA W ZASIĘGU: ' . $enemyId);
                            $path = $this->getPathToMyArmyInRange();
                            if ($path->current) {
                                $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                                $this->_Computer->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                                return $this->endMove($this->_Computer->id, $path);
                            }
                            $this->_l->log('BRAK MOJEJ ARMII W ZASIĘGU - IDŹ W KIERUNKU ZAMKU!');
                            return $this->savePath($pathToNearestWeakestHostileCastle);
                        } else {
                            $this->_l->log('BRAK SILNIEJSZEJ ARMII WROGA W ZASIĘGU - IDŹ W KIERUNKU ZAMKU!');
                            return $this->savePath($pathToNearestWeakestHostileCastle);
                        }
                    }
                }
            } else {
                $this->_l->log('BRAK SŁABSZYCH ZAMKÓW WROGA');
                return $this->noEnemyCastlesToAttack();
            }
        }
    }

    private function noEnemyCastlesToAttack()
    {
        $this->_l->logMethodName();
        if (!$this->_enemies) {
            // brak zamków i armii wroga - koniec gry
            $mTurn = new Cli_Model_Turn($this->_user, $this->_db, $this->_gameHandler);
            $mTurn->endGame($this->_mGame);
            return;
        }

        foreach ($this->_enemies as $e) {
            if (Application_Model_Board::isCastleAtPosition($e->x, $e->y, $this->_map['hostileCastles']) !== null) {
                // pomijam wrogów w zamku
                continue;
            }
            if ($this->isEnemyStronger($e)) {
                // pomijam silniejszych wrogów
                continue;
            }
            $enemy = $e;
            break;
        }
        if (isset($enemy)) {
            // ATAKUJ
            $this->_l->log('WRÓG JEST SŁABSZY');
            $path = $this->isEnemyArmyInRange($enemy);
            if (empty($path->current)) {
                $this->_l->log('SŁABSZY WRÓG POZA ZASIĘGIEM - IDŹ DO WROGA');
                return $this->savePath($path);
            }
            $this->_l->log('SŁABSZY WRÓG W ZASIĘGU - ATAKUJ!');
            $fightEnemyResults = $this->fightEnemy($path);
            return $this->endMove($this->_Computer->id, $path, $fightEnemyResults);

        } else {
            $this->_l->log('WRÓG JEST SILNIEJSZY');
            $path = $this->getPathToMyArmyInRange();
            if ($path) {
                $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                $this->_Computer->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                return $this->endMove($this->_Computer->id, $path);
            } else {
                $this->_l->log('BRAK MOJEJ ARMII W ZASIĘGU');
                $path = $this->getPathToMyCastle($this->getMyCastleNearEnemy());
                if (isset($path->current) && $path->current) {
                    $this->_l->log('JEST MÓJ ZAMEK W POBLIŻU WROGA - IDŹ DO ZAMKU');
                    return $this->savePath($path);
                } else {
                    $this->_l->log('NIE MA MOJEGO ZAMKU W POBLIŻU WROGA - ZOSTAŃ');
                    $this->_mArmyDB->fortify($this->_Computer->id, 1);
                    return $this->endMove();
                }
            }
        }
    }

    private function ruinBlock()
    {
        $this->_l->logMethodName();
        if (empty($this->_Computer->heroes)) {
            $this->_l->log('BRAK HEROSA');
            return $this->firstBlock();
        }

        $this->_l->log('JEST HEROS');
        $mRuinsInGame = new Application_Model_RuinsInGame($this->_gameId, $this->_db);
        $path = $this->getPathToNearestRuin($mRuinsInGame->getFullRuins());

        if (!$path) {
            $this->_l->log('BRAK RUIN');
            return $this->firstBlock();
        }

        $this->_l->log('IDĘ DO RUIN');
        $this->_Computer->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
        Cli_Model_SearchRuin::search($this->_gameId, $path->ruinId, $this->_Computer->heroes[0]['heroId'], $this->_Computer->id, $this->_playerId, $this->_db);
        $this->_mArmyDB->fortify($this->_Computer->id, 1);
        return $this->endMove($this->_Computer->id, $path);

    }

    private function savePath($move)
    {
        $this->_l->logMethodName();
        if (!isset($move->full)) {
            var_dump($move);
            throw new Exception('aaa');
            exit;
        }

        if (empty($move->full)) {
            var_dump($move);
            throw new Exception('bbb');
            exit;
        }

        $this->_l->log('ZAPISUJĘ ŚCIEŻKĘ');

        $newPath = array();
        $start = false;

        foreach ($move->full as $step) {
            if ($move->x == $step['x'] && $move->y == $step['y']) {
                $start = true;
            }

            if ($start) {
                $newPath[] = $step;
            }
        }

        $this->_user->parameters['computer'][$this->_playerId][$this->_Computer->id] = array(
            'path' => $newPath
        );

        $this->_Computer->updateArmyPosition($this->_playerId, $move, $this->_map['fields'], $this->_gameId, $this->_db);
        $this->_mArmyDB->fortify($this->_Computer->id, 1);
        return $this->endMove($this->_Computer->id, $move);
    }

    private function goByThePath()
    {
        $this->_l->logMethodName();

        $this->_l->log('IDĘ ŚCIEŻKĄ');
        $path = $this->_Computer->calculateMovesSpend($this->_user->parameters['computer'][$this->_playerId][$this->_Computer->id]['path']);
        unset($this->_user->parameters['computer'][$this->_playerId][$this->_Computer->id]['path']);

        if ($path->end['tt'] == 'E') {
            $this->_l->log('JEST KONIEC ŚCIEŻKI');
            $path->castleId = Application_Model_Board::isCastleAtPosition($path->x, $path->y, $this->_map['hostileCastles']);
            if ($path->castleId) {
                $this->_l->log('JEST ZAMEK - WALCZĘ');
                $fightEnemyResults = $this->fightEnemy($path);
            } else {
                $this->_l->log('BRAK ZAMKU');
                if ($this->_mArmyDB->isEnemyArmyAtPosition($path->end, $this->_playerId)) {
                    $this->_l->log('JEST WROGA ARMIA - WALCZĘ');
                    $fightEnemyResults = $this->fightEnemy($path);
                } else {
                    $this->_l->log('WRÓG ZMIENIŁ POZYCJĘ');
                    return $this->move();
                }
            }

            return $this->endMove($this->_Computer->id, $path, $fightEnemyResults);
        }

        if (count($path->full) > count($path->current)) {
            return $this->savePath($path);
        }

        $this->_Computer->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
        $this->_mArmyDB->fortify($this->_Computer->id, 1);
        return $this->endMove($this->_Computer->id, $path);
    }

    private function endMove($oldArmyId = null, Cli_Model_Path $path = null, Cli_Model_FightResult $fightResults = null)
    {
        $this->_l->logMethodName();

        if (!$oldArmyId) {
            $oldArmyId = $this->_Computer->id;
        }

        if ($fightResults) {
            if ($fightResults->victory) {
                $joinIds = Cli_Model_Army::joinArmiesAtPosition($path->end, $this->_playerId, $this->_gameId, $this->_db);
            } else {
                $joinIds = array('deletedIds' => null);
            }
            $attackerArmy = $fightResults->attackerArmy;
            $currentPath = $path->current;
        } else {
            if (isset($path->current) && $path->current) {
                $joinIds = Cli_Model_Army::joinArmiesAtPosition($path->end, $this->_playerId, $this->_gameId, $this->_db);
                $attackerArmy = Cli_Model_Army::getArmyByArmyIdPlayerId($joinIds['armyId'], $this->_playerId, $this->_gameId, $this->_db);
                $currentPath = $path->current;
            } else {
                $joinIds = array('deletedIds' => null);
                $attackerArmy = $this->_Computer->getArmy();
                $currentPath = null;
            }
            $fightResults = new Cli_Model_FightResult();
        }

        if (isset($path->castleId)) {
            $castleId = $path->castleId;
        } else {
            $castleId = null;
        }

        if (isset($path->ruinId)) {
            $ruinId = $path->ruinId;
        } else {
            $ruinId = null;
        }

        if (empty($attackerArmy)) {
            print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4));
            exit;
        }

        new Cli_Model_TowerHandler($currentPath, $this->_playerId, $this->_gameId, $this->_db, $this->_gameHandler);

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'attackerColor' => $playersInGameColors[$this->_playerId],
            'attackerArmy' => $attackerArmy,
            'defenderColor' => $fightResults->defenderColor,
            'defenderArmy' => $fightResults->defenderArmy,
            'path' => $currentPath,
            'battle' => $fightResults->battle,
            'victory' => $fightResults->victory,
            'deletedIds' => $joinIds['deletedIds'],
            'oldArmyId' => $oldArmyId,
            'castleId' => $castleId,
            'ruinId' => $ruinId,
            'type' => 'computer'
        );

        $this->_gameHandler->sendToChannel($this->_db, $token, $this->_gameId);
    }


}

