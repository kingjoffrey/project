<?php

class Cli_Model_ComputerMove extends Cli_Model_ComputerMethods
{
    private $_searchRuin = false;

    public function __construct(Cli_Model_Army $army, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        parent::__construct($army, $user, $db, $gameHandler);
        $this->_l = new Coret_Model_Logger();
        $this->_l->log('');
        $this->_l->log($this->_playerId, 'playerId: ');
        $this->_l->log($this->_color, 'color: ');
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
        if ($this->_game->getNumberOfGarrisonUnits()) {
            $garrison = new Cli_Model_Garrison($myCastle->getX(), $myCastle->getY(), $this->_color, $this->_player->getArmies(), $this->_game, $this->_db, $this->_gameHandler);
            if ($armyId = $garrison->getNewArmyId()) {
                $this->_l->log('NOWA ARMIA');
                $this->_army = $this->_player->getArmies()->getArmy($armyId);
                $this->_armyId = $this->_army->getId();
                $this->_armyX = $this->_army->getX();
                $this->_armyY = $this->_army->getY();
                $this->_movesLeft = $this->_army->getMovesLeft();
            } else {
                $this->_army->setFortified(true);
                $this->next();
                return;
            }
        }

        $this->firstBlock();
    }

    private function outside()
    {
        $this->_l->logMethodName();
        $this->_l->log('POZA ZAMKIEM');
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
                $this->_army->setFortified(1);
                $this->move($path);
                return;
            }
            $this->_l->log('BRAK MOJEJ ARMII W ZASIĘGU - IDŹ W KIERUNKU ZAMKU WROGA!');
            return $this->savePathAndMove($nwhc->getPath());
        }

        $this->_l->log('BRAK SILNIEJSZEJ ARMII WROGA W ZASIĘGU - IDŹ W KIERUNKU ZAMKU WROGA!');
        return $this->savePathAndMove($nwhc->getPath());
    }

    private function noEnemyCastlesToAttack()
    {
        $this->_l->logMethodName();
        foreach ($this->_players->getEnemies($this->_color) as $e) {
            if ($this->_fields->getField($e->getX(), $e->getY())->getCastleId()) {
                // pomijam wrogów w zamku
                continue;
            }
            $heuristics = new Cli_Model_Heuristics($e->getX(), $e->getY());
            if ($heuristics->calculateH($this->_army->getX(), $this->_army->getY()) > $this->_army->getMovesLeft()) {
                // pomijam tych za daleko
                continue;
            }
            $es = new Cli_Model_EnemyStronger($this->_army, $this->_game, $e->getX(), $e->getY(), $this->_color);
            if ($es->stronger()) {
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
            if ($path && $path->enemyInRange()) {
                $this->_l->log('SŁABSZY WRÓG W ZASIĘGU - ATAKUJ!');
                $this->move($path);
                return;
            }

            $this->_l->log('SŁABSZY WRÓG POZA ZASIĘGIEM - IDŹ DO WROGA');
            $this->savePathAndMove($path);
            return;
        } else {
            $this->_l->log('WRÓG JEST SILNIEJSZY');
            $path = $this->getPathToMyArmyInRange();
            if ($path) {
                if ($path->targetWithin()) {
                    $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                } else {
                    $this->_l->log('JEST MOJA ARMIA PRAWIE W ZASIĘGU - IDŹ DO MOJEJ ARMII!');
                }
                $this->move($path);
            } else {
                $path = $this->getPathToMyClosestArmy();
                if ($path) {
                    $this->_l->log('JEST MOJA ARMIA POZA ZASIĘGIEM - IDŹ DO MOJEJ ARMII!');
                    $this->move($path);
                } else {
                    $this->_l->log('JEST MÓJ ZAMEK - IDŹ DO MOJEGO ZAMKU!');
                    $path = $this->getPathToMyClosestCastle();
                    $this->move($path);
                }
            }
        }
    }

    private function ruinBlock()
    {
        $this->_l->logMethodName();
        if (!$heroId = $this->_army->getHeroes()->getAnyHeroId()) {
            $this->_l->log('BRAK HEROSA');
            $this->firstBlock();
            return;
        }

        $this->_l->log('JEST HEROS');
        if ($ruinId = $this->_fields->getField($this->_armyX, $this->_armyY)->getRuinId()) {
            if (!$this->_game->getRuins()->getRuin($ruinId)->getEmpty()) {
                $this->_l->log('PRZESZUKUJĘ RUINY');
                $this->_game->getRuins()->getRuin($ruinId)->search($this->_game, $this->_army, $heroId, $this->_playerId, $this->_db, $this->_gameHandler);
                $this->_searchRuin = false;
                $this->next();
                return;
            }
        }

        $ptnr = new Cli_Model_PathToNearestRuin($this->_game, $this->_army);
        if ($ruinId = $ptnr->getRuinId()) {
            $this->_l->log('IDĘ DO RUIN');
            $this->_searchRuin = true;
            $this->move($ptnr->getPath());
        } else {
            $this->_l->log('BRAK RUIN');
            $this->firstBlock();
        }
    }

    private function savePathAndMove(Cli_Model_Path $path)
    {
        $this->_l->log('ZAPISUJĘ ŚCIEŻKĘ');
        $this->_army->saveOldPath($path);
        $this->_army->setFortified(true);
        $this->move($path);
    }

    private function goByThePath()
    {
        $this->_l->log('IDĘ ŚCIEŻKĄ');
        $this->_l->log($this->_armyId, 'armyId: ');

        $path = new Cli_Model_Path($this->_army->getOldPath(), $this->_army, $this->_game->getTerrain());
        $current = array();

        foreach ($path->getCurrent() as $step) {
            $current[] = $step;
            if ($fieldArmies = $this->_fields->getField($step['x'], $step['y'])->getArmies()) {
                foreach ($fieldArmies as $armyId => $color) {
                    if ($color == $this->_color) {
                        $path->setCurrent($current);
                        $this->savePathAndMove($path);
                        return;
                    }
                }
            }
            $enemies = new Cli_Model_Enemies($this->_game, $step['x'], $step['y'], $this->_color);
            if ($enemies->hasEnemies()) {
                $path->setCurrent($current);
                $this->savePathAndMove($path);
                return;
            }
        }

        $this->_army->resetOldPath();
        $this->move($path);

        return;
    }


    private function move(Cli_Model_Path $path = null)
    {
        if ($path && $path->exists()) {
            $this->_l->log('(armyId=' . $this->_army->getId() . ')IDĘ/WALCZĘ');
            $this->_army->move($this->_game, $path, $this->_db, $this->_gameHandler);
            $this->next();
        } else {
            $this->_l->log('(armyId=' . $this->_army->getId() . ')BRAK ŚCIEŻKI');
            $this->_army->setFortified(true);
            $this->next();
        }
    }

    private function next()
    {
        if ($this->_army = $this->_player->getArmies()->getComputerArmyToMove()) {
            $this->_l->log('(armyId=' . $this->_army->getId() . ')BIORĘ KOLEJNĄ ARMIĘ');
            $this->_armyId = $this->_army->getId();
            $this->_armyX = $this->_army->getX();
            $this->_armyY = $this->_army->getY();
            $this->_movesLeft = $this->_army->getMovesLeft();
            if ($this->_army->hasOldPath()) {
                $this->goByThePath();
            } elseif ($this->_searchRuin) {
                $this->ruinBlock();
            } else {
                $this->findPath();
            }
        } else {
            $this->_l->log('NASTĘPNA TURA');
            new Cli_Model_NextTurn($this->_user, $this->_db, $this->_gameHandler);
        }
    }
}