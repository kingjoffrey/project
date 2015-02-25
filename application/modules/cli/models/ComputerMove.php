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
$aaa=$this->_game->getPlayers()->getPlayer($this->_color)->getArmies()->getArmy($this->_army->getId());
        if ($this->_army->hasOldPath()) {
            $this->goByThePath();
        } else {
            $this->findPath();
        }
    }

    private function findPath()
    {
        $this->_l->logMethodName();
        $this->_l->log($this->_armyId, 'armyId: ');
        $aaa=$this->_game->getPlayers()->getPlayer($this->_color)->getArmies()->getArmy($this->_army->getId());
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
        $aaa=$this->_game->getPlayers()->getPlayer($this->_color)->getArmies()->getArmy($this->_army->getId());
        $myCastle = $this->_player->getCastles()->getCastle($castleId);
        if ($numberOfUnits = $this->_game->getNumberOfGarrisonUnits()) {
            $garrison = new Cli_Model_Garrison($numberOfUnits, $myCastle->getX(), $myCastle->getY(), $this->_player->getArmies(), $this->_user, $this->_game, $this->_db, $this->_gameHandler);
            $armyToGo = $garrison->getArmyToGo();
            if ($armyToGo) {
                $this->_army = $armyToGo;
                $this->_armyId = $this->_army->getId();
                $this->_armyX = $this->_army->getX();
                $this->_armyY = $this->_army->getY();
                $this->_movesLeft = $this->_army->getMovesLeft();
            } else {
                $this->move();
                return;
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
                    $path = $this->getPathToEnemyInRange($enemy);
                    $this->move($path);
                    return;
                }

                $this->_l->log('WRÓG JEST SILNIEJSZY - ZOSTAŃ!');
                $this->move();
                return;
            }
        } else {
            $this->_l->log('JEST WRÓG Z ZASIĘGIEM');

            if ($this->_game->getTurnNumber() <= 7 && !$enemiesInRange) {
                $this->_l->log('BRAK WROGA W ZASIĘGU I TURA < 8 - ZOSTAŃ!');
                $this->move();
                return;
            } else {
                $this->_l->log('JEST WRÓG W ZASIĘGU');

                if ($this->_game->getTurnNumber() <= 7 && count($enemiesHaveRange) > 1) {
                    $this->_l->log('WRÓGÓW Z ZASIĘGIEM > WRÓGÓW W ZASIĘGU - ZOSTAŃ!');
                    $this->move();
                    return;
                } else {
                    $this->_l->log('TYLKO JEDEN Z WRÓGÓW Z ZASIĘGIEM LUB TURA > 7');

                    $enemy = $this->canAttackAllEnemyHaveRange($enemiesHaveRange); // todo zoptymalizować
                    if (!$enemy) {
                        $this->_l->log('NIE MOGĘ ZAATAKOWAĆ WRÓGÓW Z ZASIĘGIEM - ZOSTAŃ!');
                        $this->move();
                        return;
                    } else {
                        $path = $this->isEnemyArmyInRange($enemy);
                        if ($path && !$path->enemyInRange()) {
                            $this->_l->log('WRÓG Z ZASIĘGIEM POZA ZASIĘGIEM - IDŹ DO WROGA!');
                            return $this->savePath($path);
                        }
                        $this->_l->log('ATAKUJĘ WRÓGA Z ZASIĘGIEM - ATAKUJ!');
                        $this->move($path);
                        return;
                    }
                }
            }
        }
    }

    private function outside()
    {
        $this->_l->logMethodName();
        $this->_l->log('POZA ZAMKIEM');
        $aaa=$this->_game->getPlayers()->getPlayer($this->_color)->getArmies()->getArmy($this->_army->getId());
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
        $aaa=$this->_game->getPlayers()->getPlayer($this->_color)->getArmies()->getArmy($this->_army->getId());
        if ($this->_players->noEnemyCastles($this->_color)) {
            $this->_l->log('BRAK ZAMKÓW WROGA');
            return $this->noEnemyCastlesToAttack();
        }

        $this->_l->log('SĄ ZAMKI WROGA');
        $nwhc = new Cli_Model_NearestWeakerHostileCastle($this->_game, $this->_color, $this->_army);
        $path = $nwhc->getPath();
        if (!$path || !$path->exists()) {
            $this->_l->log('BRAK SŁABSZYCH ZAMKÓW WROGA');
            return $this->noEnemyCastlesToAttack();
        }

        $this->_l->log('JEST SŁABSZY ZAMEK WROGA');
        if ($path && $path->enemyInRange()) {
            $this->_l->log('SŁABSZY ZAMEK WROGA W ZASIĘGU - ATAKUJĘ!');
            $this->move($path);
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
        $aaa=$this->_game->getPlayers()->getPlayer($this->_color)->getArmies()->getArmy($this->_army->getId());
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
                    $this->move();
                    return;
                }
            }
        }
    }

    private function ruinBlock()
    {
        $this->_l->logMethodName();
        $aaa=$this->_game->getPlayers()->getPlayer($this->_color)->getArmies()->getArmy($this->_army->getId());
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
        $aaa=$this->_game->getPlayers()->getPlayer($this->_color)->getArmies()->getArmy($this->_army->getId());
        $this->_army->saveOldPath($path);
        $this->_army->setFortified(true, $this->_gameId, $this->_db);
        $this->move($path);
    }

    private function goByThePath()
    {
        $this->_l->log('IDĘ ŚCIEŻKĄ');
        $aaa=$this->_game->getPlayers()->getPlayer($this->_color)->getArmies()->getArmy($this->_army->getId());
        $this->_l->log($this->_armyId, 'armyId: ');

        $path = new Cli_Model_Path($this->_army->getOldPath(), $this->_army);

        if (!$path->enemyInRange()) {
            $this->savePath($path);
        } else {
            $this->_army->resetOldPath();
            $this->move($path);
        }
        return;
    }

    private function move(Cli_Model_Path $path = null)
    {
        $this->_l->log('IDĘ... LUB WALCZĘ');
        $aaa=$this->_game->getPlayers()->getPlayer($this->_color)->getArmies()->getArmy($this->_army->getId());
        if ($path && $path->exists()) {
            $this->_army->move($this->_game, $path, $this->_db, $this->_gameHandler);
        } else {
            $this->_army->setFortified(true, $this->_gameId, $this->_db);
            if ($this->_army = $this->_player->getArmies()->getComputerArmyToMove()) {
                $this->_armyId = $this->_army->getId();
                $this->_armyX = $this->_army->getX();
                $this->_armyY = $this->_army->getY();
                $this->_movesLeft = $this->_army->getMovesLeft();
                if ($this->_army->hasOldPath()) {
                    $this->goByThePath();
                } else {
                    $this->findPath();
                }
            } else {
                $this->_l->log('NASTĘPNA TURA');
                new Cli_Model_NextTurn($this->_game, $this->_db, $this->_gameHandler);
            }
        }
    }
}