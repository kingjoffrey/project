<?php

class Cli_Model_ComputerMove extends Cli_Model_ComputerMethods
{
    public function __construct(Cli_Model_Army $army, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $this->_army = $army;
        $this->_user = $user;
        $this->_game = $game;
        $this->_db = $db;
        $this->_gameHandler = $gameHandler;

        $this->_playerId = $this->_game->getTurnPlayerId();

        $this->_players = $this->_game->getPlayers();
        $this->_color = $this->_game->getPlayerColor($this->_playerId);
        $this->_player = $this->_players->getPlayer($this->_color);

        $this->_armyId = $this->_army->getId();
        $this->_armyX = $this->_army->getX();
        $this->_armyY = $this->_army->getY();

        $this->_gameId = $this->_game->getId();
        $this->_fields = $this->_game->getFields();

        $this->_l = new Coret_Model_Logger();
        $this->_l->log('');
        $this->_l->log($this->_playerId, 'playerId: ');
        $this->_l->log($this->_color, 'color: ');
        $this->_l->log($this->_armyId, 'armyId: ');

        if (isset($this->_user->parameters['computer'][$this->_playerId][$this->_armyId]['path']) && $this->_user->parameters['computer'][$this->_playerId][$this->_armyId]['path']) {
            return $this->goByThePath();
        }
        $this->findPath();
    }

    private function findPath()
    {
        $this->_l->logMethodName();

        if ($castleId = $this->_fields->isPlayerCastle($this->_color, $this->_armyX, $this->_armyY)) {
            return $this->inside($this->_player->getCastles()->getCastle($castleId));
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
                        if ($armyId == $army->getId()) {
                            continue;
                        }
                        $notGarrison[] = $army;
                    }

                    if (count($notGarrison) > 1) {
                        $this->_l->log('ŁĄCZĘ ARMIE, KTÓRE PÓJDĄ DALEJ');

                        $firstArmy = current($notGarrison);

                        $this->_path = new Cli_Model_Path(array(0 => array(
                            'x' => $firstArmy['x'],
                            'y' => $firstArmy['y'],
                            'tt' => 'c')
                        ), $firstArmy);
                        $this->_army = next($notGarrison);
                        return;
                    } elseif (count($notGarrison) == 1) {
                        $this->_l->log('TA ARMIA IDZIE DALEJ');
                        $this->_army = current($notGarrison);
                    }
                } else {
                    $this->_l->log('OBSADA ZAMKU - ZOSTAŃ!');
                    $this->_army = current($garrison);
                    return;
                }
            } elseif (count($garrison) > 1) {
                $this->_l->log('ŁĄCZĘ ARMIE W JEDNĄ');

                $firstArmy = current($garrison);
                $this->_path = new Cli_Model_Path(array(0 => array(
                    'x' => $firstArmy->getX(),
                    'y' => $firstArmy->getY(),
                    'tt' => 'c')
                ), $firstArmy);
                $this->_army = next($garrison);
                return;
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
                        $this->_path = new Cli_Model_Path(array(0 => array(
                            'x' => $castlePosition['x'] + 1,
                            'y' => $castlePosition['y'] + 1,
                            'tt' => 'c')
                        ), $army);
                    } else {
                        $this->_path = new Cli_Model_Path(array(0 => array(
                            'x' => $castlePosition['x'],
                            'y' => $castlePosition['y'],
                            'tt' => 'c')
                        ), $army);
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

            $castleId = $this->findNearestWeakestHostileCastle();

            if ($this->_path->exists()) {
                $this->_l->log('JEST SŁABSZY ZAMEK WROGA: ' . $castleId);

                if ($castleId) {
                    $this->_l->log('SŁABSZY ZAMEK WROGA W ZASIĘGU - ATAKUJĘ!');
                    $this->_army->move($this->_game, $this->_path, $this->_color, $this->_db, $this->_gameHandler);
                    return;
                } else {
                    $this->_l->log('SŁABSZY ZAMEK WROGA POZA ZASIĘGIEM');
                    $this->getWeakerEnemyArmyInRange();
                    if ($this->_path->exists()) {
                        //atakuj
                        $this->_l->log('JEST SŁABSZA ARMIA WROGA W ZASIĘGU (' . $path->armyId . ') - ATAKUJĘ!');
                        $this->_army->move($this->_game, $this->_path, $this->_color, $this->_db, $this->_gameHandler);
                        return;
                    } else {
                        $this->_l->log('BRAK SŁABSZEJ ARMII WROGA W ZASIĘGU');
                        $enemyId = $this->getStrongerEnemyArmyInRange();
                        if ($enemyId) {
                            $this->_l->log('JEST SILNIEJSZA ARMIA WROGA W ZASIĘGU: ' . $enemyId);
                            $this->getPathToMyArmyInRange();
                            if ($this->_path->exists()) {
                                $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                                $this->_army->move($this->_game, $this->_path, $this->_color, $this->_db, $this->_gameHandler);
                                return;
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
//        if (!$this->_enemies) {
//            // brak zamków i armii wroga - koniec gry
//            $mTurn = new Cli_Model_Turn($this->_user, $this->_game, $this->_db, $this->_gameHandler);
//            $mTurn->endGame();
//            return;
//        }


        foreach ($this->_players->getEnemies($this->_color) as $e) {
            if ($this->_fields->getCastleId($e->getX(), $e->getY())) {
                // pomijam wrogów w zamku
                continue;
            }
            if ($this->isEnemyStronger(array($e))) {
                // pomijam silniejszych wrogów
                continue;
            }
            $enemy = $e;
            break;
        }

        if (isset($enemy)) {
            // ATAKUJ
            $this->_l->log('WRÓG JEST SŁABSZY');
            $this->isEnemyArmyInRange($enemy);
            echo $this->_path->getX() . "\n";
            echo $this->_path->getY() . "\n";
            echo $enemy->getX() . "\n";
            echo $enemy->getY() . "\n";
            if ($this->_path->exists() && $this->_path->getX() == $enemy->getX() && $this->_path->getY() == $enemy->getY()) {
                $this->_l->log('SŁABSZY WRÓG W ZASIĘGU - ATAKUJ!');
                $this->_army->move($this->_game, $this->_path, $this->_color, $this->_db, $this->_gameHandler);
                return;
            }
            $this->_l->log('SŁABSZY WRÓG POZA ZASIĘGIEM - IDŹ DO WROGA');
            $this->savePath();
            return;
        } else {
            $this->_l->log('WRÓG JEST SILNIEJSZY');
            $path = $this->getPathToMyArmyInRange();
            if ($path) {
                $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                $this->_army->updateArmyPosition($this->_playerId, $path, $this->_map['fields'], $this->_gameId, $this->_db);
                return $this->endMove($this->_armyId, $path);
            } else {
                $this->_l->log('BRAK MOJEJ ARMII W ZASIĘGU');
                $castle = $this->getMyCastleNearEnemy();
                if ($castle) {
                    $this->getPathToMyCastle($castle);
                }
                if ($this->_path->exists()) {
                    $this->_l->log('JEST MÓJ ZAMEK W POBLIŻU WROGA - IDŹ DO ZAMKU');
                    return $this->savePath();
                } else {
                    $this->_l->log('NIE MA MOJEGO ZAMKU W POBLIŻU WROGA - ZOSTAŃ');
                    $this->_army->setFortified(true, $this->_gameId, $this->_db);
                    $this->_army->move($this->_game, $this->_path, $this->_color, $this->_db, $this->_gameHandler);
                    return;
                }
            }
        }
    }

    private function ruinBlock()
    {
        $this->_l->logMethodName();
        if (!$this->_army->getHeroes()->exists()) {
            $this->_l->log('BRAK HEROSA');
            return $this->firstBlock();
        }

        $this->_l->log('JEST HEROS');
        $ruinId = $this->getPathToNearestRuin($this->_playerId, $this->_army);

        if (empty($ruinId)) {
            $this->_l->log('BRAK RUIN');
            return $this->firstBlock();
        }

        $this->_l->log('IDĘ DO RUIN');
        $this->_army->move($this->_game, $this->_path, $this->_color, $this->_db, $this->_gameHandler);
        $this->_game->getRuins()->getRuin($ruinId)->search($this->_game, $this->_army, $this->_playerId, $this->_db);
        $this->_army->setFortified(true, $this->_gameId, $this->_db);
    }

    private function savePath()
    {
        $this->_l->logMethodName();
        if (!$this->_path->hasFull()) {
            var_dump($this->_path);
            throw new Exception('bbb');
            exit;
        }

        $this->_l->log('ZAPISUJĘ ŚCIEŻKĘ');

        $newPath = array();
        $start = false;

        foreach ($this->_path->getFull() as $step) {
            if ($this->_path->getX() == $step['x'] && $this->_path->getY() == $step['y']) {
                $start = true;
            }

            if ($start) {
                $newPath[] = $step;
            }
        }

        $this->_user->parameters['computer'][$this->_playerId][$this->_armyId] = array(
            'path' => $newPath
        );

        $this->_army->move($this->_game, $this->_path, $this->_color, $this->_db, $this->_gameHandler);
        $this->_army->setFortified(true, $this->_gameId, $this->_db);
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
                    return $this->findPath();
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

    public function move()
    {
        if (empty($this->_path->current)) {
            return;
        }

        $this->_army->move($this->_game, $this->_path, $this->_color, $this->_db, $this->_gameHandler);
    }
}