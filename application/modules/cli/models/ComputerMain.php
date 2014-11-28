<?php

class Cli_Model_ComputerMain extends Cli_Model_ComputerMethods
{
    public function __construct(IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $this->_user = $user;
        $this->_game = $game;
        $this->_db = $db;
        $this->_gameHandler = $gameHandler;

        $this->_l = new Coret_Model_Logger();
        $this->_playerId = $this->_game->getTurnPlayerId();

        $this->_players = $this->_game->getPlayers();
        $this->_color = $this->_game->getPlayerColor($this->_playerId);
        $this->_player = $this->_players->getPlayer($this->_color);

        if (!$this->_player->getTurnActive()) {
            $this->_l->log('START TURY');
            $mTurn = new Cli_Model_Turn($this->_user, $this->_game, $this->_db, $this->_gameHandler);
            $mTurn->start($this->_playerId, true);
            return;
        }

        if (!$this->_player->getComputer()) {
            echo 'To (' . $this->_playerId . ') nie komputer!' . "\n";
//                $this->sendError($user, 'To (' . $playerId . ') nie komputer!');
            return;
        }

        if (Cli_Model_ComputerHeroResurrection::handle($this->_playerId, $this->_game, $this->_db, $this->_gameHandler)) {
            return;
        }

        if ($this->_army = $this->_player->getComputerArmyToMove()) {
            $this->_armyId = $this->_army->getId();
            $this->_armyX = $this->_army->getX();
            $this->_armyY = $this->_army->getY();

            $this->_gameId = $this->_game->getId();
            $this->_fields = $this->_game->getFields();

            $this->_l->log('');
            $this->_l->log($this->_playerId, 'playerId: ');
            $this->_l->log($this->_color, 'color: ');
            $this->_l->log($this->_armyId, 'armyId: ');

            if (isset($this->_user->parameters['computer'][$this->_playerId][$this->_armyId]['path']) && $this->_user->parameters['computer'][$this->_playerId][$this->_armyId]['path']) {
                return $this->goByThePath();
            }
            $this->move();
        } else {
            $this->_l->log('NASTĘPNA TURA');
            $mTurn = new Cli_Model_Turn($this->_user, $this->_game, $this->_db, $this->_gameHandler);
            $mTurn->next($playerId);
        }
    }

    private function move()
    {
        $this->_l->logMethodName();

        if ($castleId = $this->_fields->isPlayerCastle($this->_color, $this->_armyX, $this->_armyY)) {
            return $this->inside($this->_player->getCastle($castleId));
        } else {
            return $this->outside();
        }
    }

    private function inside($myCastle)
    {
        $this->_l->logMethodName();
        $this->_l->log('W ZAMKU');

        $numberOfUnits = floor($this->_game->getTurnNumber() / 7);
        if ($numberOfUnits > 4) {
            $numberOfUnits = 4;
        }

        if ($numberOfUnits) {
            $garrison = $this->_players->getArmiesFromCastle($myCastle->getId());
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

                        $this->_army = new Cli_Model_Army($secondArmy);
                        $this->_army->move($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                        return $this->endMove($secondArmy['armyId'], $path);
                    } elseif (count($notGarrison) == 1) {
                        $this->_l->log('TA ARMIA IDZIE DALEJ');
                        $this->_army = new Cli_Model_Army(current($notGarrison));
                    }
                } else {
                    $this->_l->log('OBSADA ZAMKU - ZOSTAŃ!');
                    $army = current($garrison);
                    $this->_army = new Cli_Model_Army($army);
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

                $this->_army = new Cli_Model_Army($secondArmy);
                $this->_army->move($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
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

                    $this->_army = new Cli_Model_Army(Cli_Model_Army::getArmyByArmyId($newArmyId, $this->_gameId, $this->_db));
                    $this->_army->move($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                    return $this->endMove($newArmyId, $path);
                }
                $this->_l->log('ZA MAŁA OBSADA ZAMKU - ZOSTAŃ!');

                $this->_army->setFortified(true, $this->_gameId, $this->_db);
                return $this->endMove();
            }
        }

        $enemiesHaveRange = $this->getEnemiesHaveRangeAtThisCastle($myCastle);
        $enemiesInRange = $this->getEnemiesInRange();
        if (!$enemiesHaveRange) {
            $this->_l->log('BRAK WROGA Z ZASIĘGIEM');

            if (!$enemiesInRange) {
                $this->_l->log('BRAK WROGA W ZASIĘGU');

                return $this->ruinBlock();
            } else {
                $this->_l->log('JEST WRÓG W ZASIĘGU');

                foreach ($enemiesInRange as $e) {
                    if ($this->isEnemyStronger(array($e))) {
                        continue;
                    } else {
                        $enemy = $e;
                        break;
                    }
                }
                if (isset($enemy)) {
                    $this->_l->log('WRÓG JEST SŁABSZY - ATAKUJ!');

                    if ($castleId = $this->_fields->getCastleId($enemy->getX(), $enemy->getY())) {
                        $path = $this->getPathToEnemyCastleInRange($castleId);
                        $path->castleId = $castleId;
                    } else {
                        $path = $this->getPathToEnemyInRange($enemy);
                    }
                    $fightEnemyResults = $this->fightEnemy($path);
                    return $this->endMove($this->_armyId, $path, $fightEnemyResults);
                }

                $this->_l->log('WRÓG JEST SILNIEJSZY - ZOSTAŃ!');
                $this->_army->setFortified(true, $this->_gameId, $this->_db);
                return $this->endMove();
            }
        } else {
            $this->_l->log('JEST WRÓG Z ZASIĘGIEM');

            if ($this->_game->getTurnNumber() <= 7 && !$enemiesInRange) {
                $this->_l->log('BRAK WROGA W ZASIĘGU I TURA < 8 - ZOSTAŃ!');

                $this->_army->setFortified(true, $this->_gameId, $this->_db);
                return $this->endMove();
            } else {
                $this->_l->log('JEST WRÓG W ZASIĘGU');

                if ($this->_game->getTurnNumber() <= 7 && count($enemiesHaveRange) > 1) {
                    $this->_l->log('WRÓGÓW Z ZASIĘGIEM > WRÓGÓW W ZASIĘGU - ZOSTAŃ!');

                    $this->_army->setFortified(true, $this->_gameId, $this->_db);
                    return $this->endMove();
                } else {
                    $this->_l->log('TYLKO JEDEN Z WRÓGÓW Z ZASIĘGIEM LUB TURA > 7');

                    $enemy = $this->_game->canAttackAllEnemyHaveRange($enemiesHaveRange);
                    if (!$enemy) {
                        $this->_l->log('NIE MOGĘ ZAATAKOWAĆ WRÓGÓW Z ZASIĘGIEM - ZOSTAŃ!');

                        $this->_army->setFortified(true, $this->_gameId, $this->_db);
                        return $this->endMove();
                    } else {
                        $path = $this->_game->isEnemyArmyInRange($enemy);
                        if (empty($path->current)) {
                            $this->_l->log('WRÓG Z ZASIĘGIEM POZA ZASIĘGIEM - IDŹ DO WROGA!');
                            return $this->savePath($path);
                        }
                        $this->_l->log('ATAKUJĘ WRÓGA Z ZASIĘGIEM - ATAKUJ!');
                        $fightEnemyResults = $this->fightEnemy($path);
                        return $this->endMove($this->_armyId, $path, $fightEnemyResults);
                    }
                }
            }
        }
    }

    private function outside()
    {
        $this->_l->logMethodName();
        $this->_l->log('POZA ZAMKIEM');

        $path = $this->getComputerEmptyCastleInComputerRange($this->_playerId, $this->_army);
        if (!$path) {
            $this->_l->log('NIE MA MOJEGO PUSTEGO ZAMKU W ZASIĘGU');
            return $this->ruinBlock();
        } else {
            $this->_l->log('JEST MÓJ PUSTY ZAMEK W ZASIĘGU');
            if (!$this->isMyCastleInRangeOfEnemy($path, $this->_map['fields'])) {
                $this->_l->log('WRÓG NIE MA ZASIĘGU NA MÓJ PUSTY ZAMEK');
                return $this->firstBlock();
            } else {
                $this->_l->log('WRÓG MA ZASIĘG NA MÓJ PUSTY ZAMEK - IDŹ DO ZAMKU!');
                $this->_army->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                return $this->endMove($this->_armyId, $path);
            }
        }
    }

    private function firstBlock()
    {
        $this->_l->logMethodName();

        if ($this->_players->noEnemyCastles($this->_color)) {
            $this->_l->log('BRAK ZAMKÓW WROGA');
            return $this->noEnemyCastlesToAttack();
        } else {
            $this->_l->log('SĄ ZAMKI WROGA');

            $pathToNearestWeakestHostileCastle = $this->findNearestWeakestHostileCastle();

            if (isset($pathToNearestWeakestHostileCastle->castleId)) {
                $this->_l->log('JEST SŁABSZY ZAMEK WROGA: ' . $pathToNearestWeakestHostileCastle->castleId);

                if ($pathToNearestWeakestHostileCastle->in) {
                    $this->_l->log('SŁABSZY ZAMEK WROGA W ZASIĘGU - ATAKUJĘ!');
                    $this->_army->move($this->_game, $pathToNearestWeakestHostileCastle, $this->_color, $this->_db, $this->_gameHandler);
                    return;
                } else {
                    $this->_l->log('SŁABSZY ZAMEK WROGA POZA ZASIĘGIEM');
                    $path = $this->getWeakerEnemyArmyInRange();
                    if (isset($path->current) && $path->current) {
                        //atakuj
                        $this->_l->log('JEST SŁABSZA ARMIA WROGA W ZASIĘGU (' . $path->armyId . ') - ATAKUJĘ!');
                        $fightEnemyResults = $this->fightEnemy($path);
                        return $this->endMove($this->_armyId, $path, $fightEnemyResults);
                    } else {
                        $this->_l->log('BRAK SŁABSZEJ ARMII WROGA W ZASIĘGU');
                        $enemyId = $this->getStrongerEnemyArmyInRange();
                        if ($enemyId) {
                            $this->_l->log('JEST SILNIEJSZA ARMIA WROGA W ZASIĘGU: ' . $enemyId);
                            $path = $this->getPathToMyArmyInRange();
                            if ($path->current) {
                                $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                                $this->_army->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                                return $this->endMove($this->_armyId, $path);
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
            $mTurn = new Cli_Model_Turn($this->_user, $this->_game, $this->_db, $this->_gameHandler);
            $mTurn->endGame();
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
            return $this->endMove($this->_armyId, $path, $fightEnemyResults);

        } else {
            $this->_l->log('WRÓG JEST SILNIEJSZY');
            $path = $this->getPathToMyArmyInRange();
            if ($path) {
                $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                $this->_army->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                return $this->endMove($this->_armyId, $path);
            } else {
                $this->_l->log('BRAK MOJEJ ARMII W ZASIĘGU');
                $path = $this->getPathToMyCastle($this->getMyCastleNearEnemy());
                if (isset($path->current) && $path->current) {
                    $this->_l->log('JEST MÓJ ZAMEK W POBLIŻU WROGA - IDŹ DO ZAMKU');
                    return $this->savePath($path);
                } else {
                    $this->_l->log('NIE MA MOJEGO ZAMKU W POBLIŻU WROGA - ZOSTAŃ');
                    $this->_army->setFortified(true, $this->_gameId, $this->_db);
                    return $this->endMove();
                }
            }
        }
    }

    private function ruinBlock()
    {
        $this->_l->logMethodName();
        if (!$this->_army->hasHero()) {
            $this->_l->log('BRAK HEROSA');
            return $this->firstBlock();
        }

        $this->_l->log('JEST HEROS');
        $path = $this->getPathToNearestRuin($this->_playerId, $this->_army);

        if (!$path) {
            $this->_l->log('BRAK RUIN');
            return $this->firstBlock();
        }

        $this->_l->log('IDĘ DO RUIN');
        $this->_army->updateArmyPosition($this->_gameId, $path, $this->_game->getFields(), $this->_db);
        $this->_game->searchRuin($path->ruinId, $this->_army, $this->_playerId, $this->_db);
        $this->_army->setFortified(true, $this->_gameId, $this->_db);
        return $this->endMove($this->_armyId, $path);

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

        $this->_user->parameters['computer'][$this->_playerId][$this->_armyId] = array(
            'path' => $newPath
        );

        $this->_army->updateArmyPosition($this->_gameId, $move, $this->_game->getFields(), $this->_db);
        $this->_army->setFortified(true, $this->_gameId, $this->_db);
        return $this->endMove($this->_armyId, $move);
    }

    private function goByThePath()
    {
        $this->_l->logMethodName();

        $this->_l->log('IDĘ ŚCIEŻKĄ');
        $path = $this->_army->calculateMovesSpend($this->_user->parameters['computer'][$this->_playerId][$this->_armyId]['path']);
        unset($this->_user->parameters['computer'][$this->_playerId][$this->_armyId]['path']);

        if ($path->end['tt'] == 'E') {
            $this->_l->log('JEST KONIEC ŚCIEŻKI');
            $path->castleId = $this->_fields->isEnemyCastle($this->_color, $path->x, $path->y);
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

            return $this->endMove($this->_armyId, $path, $fightEnemyResults);
        }

        if (count($path->full) > count($path->current)) {
            return $this->savePath($path);
        }

        $this->_army->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
        $this->_army->setFortified(true, $this->_gameId, $this->_db);
        return;
    }
}