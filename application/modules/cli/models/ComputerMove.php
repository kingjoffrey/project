<?php

class Cli_Model_ComputerMove extends Cli_Model_ComputerMethods
{

    public function __construct(Cli_Model_Army $army, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        parent::__construct($army, $user, $game, $db, $gameHandler);
        $this->_l = new Coret_Model_Logger();
        $this->_l->log('');
        $this->_l->log($this->_playerId, 'playerId: ');
        $this->_l->log($this->_color, 'color: ');
        $this->_l->log($this->_armyId, 'armyId: ');

        if ($this->_army->hasOldPath()) {
            $this->goByThePath();
        } else {
            $this->findPath();
        }
    }

    private function findPath()
    {
        $this->_l->logMethodName();

        if ($castleId = $this->_fields->isPlayerCastle($this->_color, $this->_armyX, $this->_armyY)) {
            return $this->inside($castleId);
        } else {
            return $this->outside();
        }
    }

    private function inside($castleId)
    {
        $this->_l->logMethodName();
        $this->_l->log('W ZAMKU');

        $myCastle = $this->_player->getCastles()->getCastle($castleId);
        if ($numberOfUnits = $this->_game->getNumberOfGarrisonUnits()) {
            $garrison = new Cli_Model_Garrison($myCastle->getX(), $myCastle->getY(), $this->_player->getArmies());
            if ($garrison->sufficient($numberOfUnits)) {
                $garrison->fortify($this->_gameId, $this->_db);
                if ($garrison->getCountGarrisonArmies() > 1) {
                    $countArmiesToGo = $garrison->getCountArmiesToGo();
                    if ($countArmiesToGo > 1) {
                        $this->_l->log('ŁĄCZĘ ARMIE, KTÓRE PÓJDĄ DALEJ');

                        $army = $garrison->getArmyToGo();
                        $this->_path = new Cli_Model_Path(array(0 => array(
                            'x' => $army->g,
                            'y' => $firstArmy['y'],
                            'tt' => 'c')
                        ), $firstArmy);
                        $this->_army = next($notGarrison);
                        return;
                    } elseif ($countArmiesToGo == 1) {
                        $this->_l->log('TA ARMIA IDZIE DALEJ');
                        $this->_army = $garrison->getArmyToGo();
                    }
                } else {
                    $this->move();
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

                    $mSplitArmy = new Cli_Model_SplitArmy($army['armyId'], $s, $h, $this->_playerId, $this->_user, $this->_game, $this->_db, $this->_gameHandler);
                    $newArmyId = $mSplitArmy->getChildArmyId();

                    if ($army['x'] == $castlePosition['x'] && $army['y'] == $castlePosition['y']) {
                        $path = new Cli_Model_Path(array(0 => array(
                            'x' => $castlePosition['x'] + 1,
                            'y' => $castlePosition['y'] + 1,
                            'tt' => 'c')
                        ), $army);
                    } else {
                        $path = new Cli_Model_Path(array(0 => array(
                            'x' => $castlePosition['x'],
                            'y' => $castlePosition['y'],
                            'tt' => 'c')
                        ), $army);
                    }

                    $this->_army = new Cli_Model_Army($army, $this->_color);
                    $this->move($path);
                    return;
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

        $path = $this->getComputerEmptyCastleInComputerRange();
        if ($path && $path->targetWithin()) {
            $this->_l->log('JEST MÓJ PUSTY ZAMEK W ZASIĘGU');
            if ($this->isMyCastleInRangeOfEnemy($path)) {
                $this->_l->log('WRÓG MA ZASIĘG NA MÓJ PUSTY ZAMEK - IDŹ DO ZAMKU!');
                $this->move($path);
                return;
            }

            $this->_l->log('WRÓG NIE MA ZASIĘGU NA MÓJ PUSTY ZAMEK');
            return $this->firstBlock();
        }
        $this->_l->log('NIE MA MOJEGO PUSTEGO ZAMKU W ZASIĘGU');
        return $this->ruinBlock();
    }

    private function firstBlock()
    {
        $this->_l->logMethodName();

        if ($this->_players->noEnemyCastles($this->_color)) {
            $this->_l->log('BRAK ZAMKÓW WROGA');
            return $this->noEnemyCastlesToAttack();
        }

        $this->_l->log('SĄ ZAMKI WROGA');
        $nwhc = new Cli_Model_NearestWeakerHostileCastle($this->_game, $this->_color, $this->_army);
        if (!$nwhc->getPath()->exists()) {
            $this->_l->log('BRAK SŁABSZYCH ZAMKÓW WROGA');
            return $this->noEnemyCastlesToAttack();
        }

        $this->_l->log('JEST SŁABSZY ZAMEK WROGA');
        if ($nwhc->getPath()->enemyInRange()) {
            $this->_l->log('SŁABSZY ZAMEK WROGA W ZASIĘGU - ATAKUJĘ!');
            $this->_army->move($this->_game, $nwhc->getPath(), $this->_color, $this->_db, $this->_gameHandler);
            return;
        }

        $this->_l->log('SŁABSZY ZAMEK WROGA POZA ZASIĘGIEM');
        $path = $this->getWeakerEnemyArmyInRange();
        if ($path && $path->enemyInRange()) {
            //atakuj
            $this->_l->log('JEST SŁABSZA ARMIA WROGA W ZASIĘGU - ATAKUJĘ!');
            $this->move($path);
            return;
        }

        $this->_l->log('BRAK SŁABSZEJ ARMII WROGA W ZASIĘGU');
        $path = $this->getStrongerEnemyArmyInRange();
        if ($path && $path->enemyInRange()) {
            $this->_l->log('JEST SILNIEJSZA ARMIA WROGA W ZASIĘGU');
            $path = $this->getPathToMyArmyInRange();
            if ($path && $path->targetWithin()) {
                $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                $this->move($path);
                return;
            }
            $this->_l->log('BRAK MOJEJ ARMII W ZASIĘGU - IDŹ W KIERUNKU ZAMKU WROGA!');
            return $this->savePath($nwhc->getPath());
        }

        $this->_l->log('BRAK SILNIEJSZEJ ARMII WROGA W ZASIĘGU - IDŹ W KIERUNKU ZAMKU WROGA!');
        return $this->savePath($nwhc->getPath());
    }

    private function noEnemyCastlesToAttack()
    {
        $this->_l->logMethodName();

        foreach ($this->_players->getEnemies($this->_color) as $enemy) {
            if ($this->_fields->getCastleId($enemy->getX(), $enemy->getY())) {
                // pomijam wrogów w zamku
                continue;
            }
            $es = new Cli_Model_EnemyStronger($this->_army, $this->_game, $enemy->getX(), $enemy->getY(), $this->_color);
            if ($es->stronger()) {
                // pomijam silniejszych wrogów
                continue;
            }
            break;
        }

        if (isset($enemy)) {
            // ATAKUJ
            $this->_l->log('WRÓG JEST SŁABSZY');
            $path = $this->isEnemyArmyInRange($enemy);
            if ($path && $path->enemyInRange()) {
                $this->_l->log('SŁABSZY WRÓG W ZASIĘGU - ATAKUJ!');
                $this->move($path);
                return;
            }
            $this->_l->log('SŁABSZY WRÓG POZA ZASIĘGIEM - IDŹ DO WROGA');
            $this->savePath($path);
            return;
        } else {
            $this->_l->log('WRÓG JEST SILNIEJSZY');
            $path = $this->getPathToMyArmyInRange();
            if ($path) {
                $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                $this->move($path);
                return;
            } else {
                $this->_l->log('BRAK MOJEJ ARMII W ZASIĘGU');
                $castle = $this->getMyCastleNearEnemy();
                if ($castle) {
                    $path = $this->getPathToMyCastle($castle);
                }
                if ($path->exists()) {
                    $this->_l->log('JEST MÓJ ZAMEK W POBLIŻU WROGA - IDŹ DO ZAMKU');
                    return $this->savePath($path);
                } else {
                    $this->_l->log('NIE MA MOJEGO ZAMKU W POBLIŻU WROGA - ZOSTAŃ');
                    $this->_army->setFortified(true, $this->_gameId, $this->_db);
                    $this->move($path);
                    return;
                }
            }
        }
    }

    private function ruinBlock()
    {
        $this->_l->logMethodName();
        if (!$heroId = $this->_army->getHeroes()->getAnyHeroId()) {
            $this->_l->log('BRAK HEROSA');
            return $this->firstBlock();
        }

        $this->_l->log('JEST HEROS');
        $ptnr = new Cli_Model_PathToNearestRuin($this->_game, $this->_army);

        if (!$ruinId = $ptnr->getRuinId()) {
            $this->_l->log('BRAK RUIN');
            return $this->firstBlock();
        }

        $this->_l->log('IDĘ DO RUIN');
        $this->move($ptnr->getPath());
        $this->_army->setFortified(true, $this->_gameId, $this->_db);

        $this->_game->getRuins()->getRuin($ruinId)->search($this->_game, $this->_army, $heroId, $this->_playerId, $this->_db, $this->_gameHandler);
    }

    private function savePath(Cli_Model_Path $path)
    {
        $this->_l->log('ZAPISUJĘ ŚCIEŻKĘ');

        $this->_army->saveOldPath($path);
        $this->move($path);
        $this->_army->setFortified(true, $this->_gameId, $this->_db);
    }

    private function goByThePath()
    {
        $this->_l->log('IDĘ ŚCIEŻKĄ');
        $path = new Cli_Model_Path($this->_army->getOldPath(), $this->_army);

        if (!$path->enemyInRange()) {
            $this->savePath($path);
        } else {
            $this->_army->resetOldPath();
            $this->move($path);
        }

        $this->_army->setFortified(true, $this->_gameId, $this->_db);
        return;
    }

    private function move(Cli_Model_Path $path = null)
    {
        $this->_l->log('IDĘ... LUB WALCZĘ');

        if ($path && $path->exists()) {
            $this->_army->move($this->_game, $path, $this->_color, $this->_db, $this->_gameHandler);
        } else {
            $token = array(
                'color' => $this->_color,
                'army' => $this->_army->toArray(),
                'path' => array(),
                'battle' => null,
                'deletedIds' => null,
                'type' => 'move'
            );

            $this->_gameHandler->sendToChannel($this->_db, $token, $this->_gameId);
        }
    }
}