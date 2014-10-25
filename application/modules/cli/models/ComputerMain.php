<?php

class Cli_Model_ComputerMain extends Cli_Model_ComputerSubBlocks
{
    protected $_gameHandler;
    protected $_l;
    protected $_mGame;
    protected $_turnNumber;
    protected $_map;
    protected $_mArmy;
    protected $_enemies;

    public function __construct($user, $playerId, $db, $gameHandler)
    {
        parent::__construct($user->parameters['gameId'], $playerId, $db);

        $this->_user = $user;
        $this->_gameHandler = $gameHandler;
        $this->_l = new Coret_Model_Logger();
        $this->_mGame = new Application_Model_Game($this->_gameId, $this->_db);
        $this->_turnNumber = $this->_mGame->getTurnNumber();
    }

    private function init($army)
    {
        $this->_mArmy = new Cli_Model_Army($army);
        $this->_l->log('');
        $this->_l->log($this->_mArmy->id, 'armyId: ');

        $this->_enemies = Cli_Model_Army::getAllEnemiesArmies($this->_gameId, $this->_db, $this->_playerId);

        $this->initMap();
    }

    public function move($mArmy)
    {
        $this->init($mArmy);

        if (isset($this->_user->parameters['computer'][$this->_playerId][$this->_mArmy->id]['path']) && $this->_user->parameters['computer'][$this->_playerId][$this->_mArmy->id]['path']) {
            return $this->goByThePath();
        }

        $myCastleId = Application_Model_Board::isCastleAtPosition($this->_mArmy->x, $this->_mArmy->y, $this->_map['myCastles']);

        if ($myCastleId !== null) {
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

                            $path = array(0 => array(
                                'x' => $firstArmy['x'],
                                'y' => $firstArmy['y'],
                                'tt' => 'c')
                            );
                            $secondArmy = next($notGarrison);

                            $this->_mArmy = new Cli_Model_Army($secondArmy);
                            $this->_mArmy->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                            return $this->endMove($secondArmy['armyId'], $path[0], $path);
                        } elseif (count($notGarrison) == 1) {
                            $this->_l->log('TA ARMIA IDZIE DALEJ');
                            $this->_mArmy = new Cli_Model_Army(current($notGarrison));
                        }
                    } else {
                        $this->_l->log('OBSADA ZAMKU - ZOSTAŃ!');
                        $army = current($garrison);
                        $this->_mArmy = new Cli_Model_Army($army);
                        return $this->endMove($armyId, $army);
                    }
                } elseif (count($garrison) > 1) {
                    $this->_l->log('ŁĄCZĘ ARMIE W JEDNĄ');

                    $firstArmy = current($garrison);
                    $path = array(0 => array(
                        'x' => $firstArmy['x'],
                        'y' => $firstArmy['y'],
                        'tt' => 'c')
                    );
                    $secondArmy = next($garrison);

                    $this->_mArmy = new Cli_Model_Army($secondArmy);
                    $this->_mArmy->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                    return $this->endMove($secondArmy['armyId'], $path[0], $path);
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
                            $path = array(0 => array(
                                'x' => $castlePosition['x'] + 1,
                                'y' => $castlePosition['y'] + 1,
                                'tt' => 'c')
                            );
                        } else {
                            $path = array(0 => array(
                                'x' => $castlePosition['x'],
                                'y' => $castlePosition['y'],
                                'tt' => 'c')
                            );
                        }

                        $this->_mArmy = new Cli_Model_Army(Cli_Model_Army::getArmyByArmyId($newArmyId, $this->_gameId, $this->_db));
                        $this->_mArmy->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                        return $this->endMove($newArmyId, $path[0], $path);
                    }
                    $this->_l->log('ZA MAŁA OBSADA ZAMKU - ZOSTAŃ!');

                    $this->_mArmyDB->fortify($this->_mArmy->id, 1);
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

                    foreach ($enemiesInRange as $e) {
                        $castleId = Application_Model_Board::isCastleAtPosition($e['x'], $e['y'], $this->_map['hostileCastles']);
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
                            $range = $this->isEnemyCastleInRange($castleId);
                        } else {
                            $range = $this->isEnemyArmyInRange($enemy);
                        }
                        $fightEnemyResults = $this->fightEnemy($range['path'], $castleId);
                        return $this->endMove($this->_mArmy->id, $range['currentPosition'], $range['path'], $fightEnemyResults, $castleId);
                    }

                    $this->_l->log('WRÓG JEST SILNIEJSZY - ZOSTAŃ!');
                    $this->_mArmyDB->fortify($this->_mArmy->id, 1);
                    return $this->endMove();
                }
            } else {
                $this->_l->log('JEST WRÓG Z ZASIĘGIEM');

                if ($this->_turnNumber <= 7 && !$enemiesInRange) {
                    $this->_l->log('BRAK WROGA W ZASIĘGU I TURA < 8 - ZOSTAŃ!');

                    $this->_mArmyDB->fortify($this->_mArmy->id, 1);
                    return $this->endMove();
                } else {
                    $this->_l->log('JEST WRÓG W ZASIĘGU');

                    if ($this->_turnNumber <= 7 && count($enemiesHaveRange) > 1) {
                        $this->_l->log('WRÓGÓW Z ZASIĘGIEM > WRÓGÓW W ZASIĘGU - ZOSTAŃ!');

                        $this->_mArmyDB->fortify($this->_mArmy->id, 1);
                        return $this->endMove();
                    } else {
                        $this->_l->log('TYLKO JEDEN Z WRÓGÓW Z ZASIĘGIEM LUB TURA > 7');

                        $enemy = $this->canAttackAllEnemyHaveRange($enemiesHaveRange);
                        if (!$enemy) {
                            $this->_l->log('NIE MOGĘ ZAATAKOWAĆ WRÓGÓW Z ZASIĘGIEM - ZOSTAŃ!');

                            $this->_mArmyDB->fortify($this->_mArmy->id, 1);
                            return $this->endMove();
                        } else {
                            $range = $this->isEnemyArmyInRange($enemy);
                            if ($range['in']) {
                                $this->_l->log('ATAKUJĘ WRÓGA Z ZASIĘGIEM - ATAKUJ!');

                                $fightEnemyResults = $this->fightEnemy($range['path'], $range['castleId']);
                                return $this->endMove($this->_mArmy->id, $range['currentPosition'], $range['path'], $fightEnemyResults, $range['castleId']);
                            }
                            $this->_l->log('WRÓG Z ZASIĘGIEM POZA ZASIĘGIEM - IDŹ DO WROGA!');
                            return $this->savePath($range);
                        }
                    }
                }
            }
        } else {
            $this->_l->log('POZA ZAMKIEM');

            $myEmptyCastle = $this->getMyEmptyCastleInMyRange();
            if (!$myEmptyCastle) {
                $this->_l->log('NIE MA MOJEGO PUSTEGO ZAMKU W ZASIĘGU');

                return $this->ruinBlock($this->_map['myCastles']);
            } else {
                $this->_l->log('JEST MÓJ PUSTY ZAMEK W ZASIĘGU');

                if (!$this->isMyCastleInRangeOfEnemy($myEmptyCastle, $this->_map['fields'])) {
                    $this->_l->log('WRÓG NIE MA ZASIĘGU NA PUSTY ZAMEK');

                    return $this->firstBlock();
                } else {
                    //idź do zamku
                    $this->_l->log('WRÓG MA ZASIĘG NA PUSTY ZAMEK - IDŹ DO ZAMKU!');

                    $this->_mArmy->updateArmyPosition($this->_playerId, $myEmptyCastle['path'], $this->_map['fields'], $this->_gameId, $this->_db);
                    return $this->endMove($this->_mArmy->id, $myEmptyCastle['currentPosition'], $myEmptyCastle['path']);
                }
            }
        }
    }

    private function firstBlock()
    {
        $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId, $this->_db);

        if (!$mCastlesInGame->enemiesCastlesExist($this->_playerId, $mPlayersInGame->selectPlayerTeamExceptPlayer($this->_playerId))) {
            $this->_l->log('BRAK ZAMKÓW WROGA');
            return $this->noEnemyCastlesToAttack();
        } else {
            $this->_l->log('SĄ ZAMKI WROGA');

            $castleRange = $this->findNearestWeakestHostileCastle();

            if ($castleRange['weakerHostileCastleId']) {
                $this->_l->log('JEST SŁABSZY ZAMEK WROGA: ' . $castleRange['weakerHostileCastleId']);

                if (isset($castleRange['in']) && $castleRange['in']) {
                    //atakuj
                    $this->_l->log('SŁABSZY ZAMEK WROGA W ZASIĘGU - ATAKUJĘ!');
                    $fightEnemyResults = $this->fightEnemy($castleRange['path'], $castleRange['weakerHostileCastleId']);
                    return $this->endMove($this->_mArmy->id, $castleRange['currentPosition'], $castleRange['path'], $fightEnemyResults, $castleRange['weakerHostileCastleId']);
                }

                $this->_l->log('SŁABSZY ZAMEK WROGA POZA ZASIĘGIEM');
                $enemy = $this->getWeakerEnemyArmyInRange();
                if ($enemy) {
                    //atakuj
                    $this->_l->log('JEST SŁABSZA ARMIA WROGA W ZASIĘGU (' . $enemy['armyId'] . ') - ATAKUJĘ!');
                    $fightEnemyResults = $this->fightEnemy($enemy['path'], $enemy['castleId']);
                    return $this->endMove($this->_mArmy->id, $enemy['currentPosition'], $enemy['path'], $fightEnemyResults, $enemy['castleId']);
                }

                $this->_l->log('BRAK SŁABSZEJ ARMII WROGA W ZASIĘGU');
                $enemy = $this->getStrongerEnemyArmyInRange();
                if ($enemy) {
                    $this->_l->log('JEST SILNIEJSZA ARMIA WROGA W ZASIĘGU: ' . $enemy['armyId']);
                    $join = $this->getPathToMyArmyInRange();
                    if ($join) {
                        $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                        $this->_mArmy->updateArmyPosition($this->_playerId, $join['path'], $this->_map['fields'], $this->_gameId, $this->_db);
                        return $this->endMove($this->_mArmy->id, $join['currentPosition'], $join['path']);
                    }
                    $this->_l->log('BRAK MOJEJ ARMII W ZASIĘGU - IDŹ W KIERUNKU ZAMKU!');
                    return $this->savePath($castleRange);
                }

                $this->_l->log('BRAK SILNIEJSZEJ ARMII WROGA W ZASIĘGU');
                $join = $this->getPathToMyArmyInRange();
                if ($join) {
                    $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                    $this->_mArmy->updateArmyPosition($this->_playerId, $join['path'], $this->_map['fields'], $this->_gameId, $this->_db);
                    return $this->endMove($this->_mArmy->id, $join['currentPosition'], $join['path']);
                }
                $this->_l->log('BRAK MOJEJ ARMII W ZASIĘGU - IDŹ W KIERUNKU ZAMKU!');
                return $this->savePath($castleRange);
            }

            $this->_l->log('BRAK SŁABSZYCH ZAMKÓW WROGA');
            return $this->noEnemyCastlesToAttack();
        }
    }

    private function noEnemyCastlesToAttack()
    {
        if (!$this->_enemies) {
            // brak zamków i armii wroga - koniec gry
            $mTurn = new Cli_Model_Turn($this->_user, $this->_db, $this->_gameHandler);
            $mTurn->endGame($this->_mGame);
            return;
        }

        foreach ($this->_enemies as $e) {
            if (Application_Model_Board::isCastleAtPosition($e['x'], $e['y'], $this->_map['hostileCastles']) !== null) {
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
            //atakuj
            $this->_l->log('WRÓG JEST SŁABSZY');
            $range = $this->isEnemyArmyInRange($enemy);
            if ($range['in']) {
                $this->_l->log('SŁABSZY WRÓG W ZASIĘGU - ATAKUJ!');
                $fightEnemyResults = $this->fightEnemy($range['path'], $range['castleId']);
                return $this->endMove($this->_mArmy->id, $range['currentPosition'], $range['path'], $fightEnemyResults);
            }
            $this->_l->log('SŁABSZY WRÓG POZA ZASIĘGIEM - IDŹ DO WROGA');
            return $this->savePath($range);

        } else {
            $this->_l->log('WRÓG JEST SILNIEJSZY');
            $join = $this->getPathToMyArmyInRange();
            if ($join) {
                $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                $this->_mArmy->updateArmyPosition($this->_playerId, $join['path'], $this->_map['fields'], $this->_gameId, $this->_db);
                return $this->endMove($this->_mArmy->id, $join['currentPosition'], $join['path']);
            }
            $this->_l->log('BRAK MOJEJ ARMII W ZASIĘGU');
            $castle = $this->getMyCastleNearEnemy();
            if ($castle) {
                $this->_l->log('JEST MÓJ ZAMEK W POBLIŻU WROGA - IDŹ DO ZAMKU');
                return $this->savePath($castle);
            }
            $this->_l->log('NIE MA MOJEGO ZAMKU W POBLIŻU WROGA - ZOSTAŃ');
            $this->_mArmyDB->fortify($this->_mArmy->id, 1);
            return $this->endMove();
        }
    }

    private function ruinBlock()
    {
        if (empty($this->_mArmy->heroes)) {
            $this->_l->log('BRAK HEROSA');
            return $this->firstBlock();
        }

        $this->_l->log('JEST HEROS');
        $mRuinsInGame = new Application_Model_RuinsInGame($this->_gameId, $this->_db);
        $ruin = $this->getNearestRuin($this->_map['fields'], $mRuinsInGame->getFullRuins());

        if (!$ruin) {
            $this->_l->log('BRAK RUIN');
            return $this->firstBlock();
        }

        $this->_l->log('IDŹ DO RUIN');
        $this->_mArmy->updateArmyPosition($this->_playerId, $ruin['path'], $this->_map['fields'], $this->_gameId, $this->_db);
        Cli_Model_SearchRuin::search($this->_gameId, $ruin['ruinId'], $this->_mArmy->heroes[0]['heroId'], $this->_mArmy->id, $this->_playerId, $this->_db);
        $this->_mArmyDB->fortify($this->_mArmy->id, 1);
        return $this->endMove($this->_mArmy->id, $ruin['currentPosition'], $ruin['path'], null, null, $ruin['ruinId']);

    }

    private function savePath($move)
    {
        if (!isset($move['fullPath'])) {
            var_dump($move);
            throw new Exception('aaa');
            exit;
        }

        if (empty($move['fullPath'])) {
            var_dump($move);
            throw new Exception('bbb');
            exit;
        }

        $this->_l->log('ZAPISUJĘ ŚCIEŻKĘ');

        $newPath = array();
        $start = false;

        foreach ($move['fullPath'] as $step) {
            if ($move['currentPosition']['x'] == $step['x'] && $move['currentPosition']['y'] == $step['y']) {
                $start = true;
            }

            if ($start) {
                $newPath[] = $step;
            }
        }

        $this->_user->parameters['computer'][$this->_playerId][$this->_mArmy->id] = array(
            'path' => $newPath
        );

        $this->_mArmy->updateArmyPosition($this->_playerId, $move['path'], $this->_map['fields'], $this->_gameId, $this->_db);
        $this->_mArmyDB->fortify($this->_mArmy->id, 1);
        return $this->endMove($this->_mArmy->id, $move['currentPosition'], $move['path']);
    }

    private function goByThePath()
    {

        $this->_l->log('IDĘ ŚCIEŻKĄ');
        $move = $this->_mArmy->calculateMovesSpend($this->_user->parameters['computer'][$this->_playerId][$this->_mArmy->id]['path']);
        unset($this->_user->parameters['computer'][$this->_playerId][$this->_mArmy->id]['path']);

        if ($move['currentPosition']['tt'] == 'E') {
            $this->_l->log('WALCZĘ');
            $castleId = Application_Model_Board::isCastleAtPosition($move['currentPosition']['x'], $move['currentPosition']['y'], $this->_map['hostileCastles']);
            if ($castleId) {
                $this->_l->log('JEST ZAMEK');
                $fightEnemyResults = $this->fightEnemy($move['path'], $castleId);
            } else {
                $this->_l->log('BRAK ZAMKU');
                $fightEnemyResults = $this->fightEnemy($move['path']);
            }

            return $this->endMove($this->_mArmy->id, $move['currentPosition'], $move['path'], $fightEnemyResults);
        }

        if (count($move['fullPath']) > count($move['path'])) {
            return $this->savePath($move);
        }

        return $this->endMove($this->_mArmy->id, $move['currentPosition'], $move['path']);
    }

    private function endMove($oldArmyId = null, $position = null, $path = null, $fightEnemyResults = null, $castleId = null, $ruinId = null)
    {
        if ($position) {
            $armiesIds = Cli_Model_Army::joinArmiesAtPosition($position, $this->_playerId, $this->_gameId, $this->_db);
            $armyId = $armiesIds['armyId'];
        } else {
            $position['x'] = $this->_mArmy->x;
            $position['y'] = $this->_mArmy->y;
        }

        if (!$oldArmyId) {
            $oldArmyId = $this->_mArmy->id;
        }

        if (!isset($armyId)) {
            $armyId = $oldArmyId;
            $armiesIds = array('deletedIds' => null);
        }

        if ($fightEnemyResults) {
            $attackerArmy = $fightEnemyResults['attackerArmy'];
            $attackerArmy['x'] = $position['x'];
            $attackerArmy['y'] = $position['y'];
            $defenderArmy = $fightEnemyResults['defenderArmy'];
        } else {
            $attackerArmy = Cli_Model_Army::getArmyByArmyIdPlayerId($armyId, $this->_playerId, $this->_gameId, $this->_db);
            $defenderArmy = null;
        }

        if (!$attackerArmy) {
            print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4));
            exit;
        }

        new Cli_Model_Tower($path, $this->_playerId, $this->_gameId, $this->_db, $this->_gameHandler);

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'defenderColor' => $fightEnemyResults['defenderColor'],
            'defenderArmy' => $defenderArmy,
            'attackerColor' => $playersInGameColors[$this->_playerId],
            'attackerArmy' => $attackerArmy,
            'battle' => $fightEnemyResults['battle'],
            'victory' => $fightEnemyResults['victory'],
            'path' => $path,
            'castleId' => $castleId,
            'ruinId' => $ruinId,
            'deletedIds' => $armiesIds['deletedIds'],
            'oldArmyId' => $oldArmyId,
            'type' => 'computer'
        );

        $this->_gameHandler->sendToChannel($this->_db, $token, $this->_gameId);

        return $this->_user;
    }


}

