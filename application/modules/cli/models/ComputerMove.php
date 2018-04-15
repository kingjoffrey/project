<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_ComputerMove extends Cli_Model_ComputerMethods
{
    private $_onMyWayToSearchRuin = false;
    private $_inCastle = false;

    /**
     * @param Cli_Model_Army $army
     * @param WebSocketTransportInterface $user
     * @param Cli_CommonHandler $handler
     */
    public function __construct(Cli_Model_Army $army, WebSocketTransportInterface $user, $handler)
    {
        parent::__construct($army, $user, $handler);
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
        $this->_inCastle = true;
        $myCastle = $this->_player->getCastles()->getCastle($castleId);

        if ($this->_player->getGold() > 100 && $myCastle->getDefense() < 2) {
            new Cli_Model_CastleBuildDefense($this->_playerId, $castleId, $this->_user, $this->_handler);
        }

        if ($this->_game->getNumberOfGarrisonUnits()) {
            $garrison = new Cli_Model_Garrison($myCastle->getX(), $myCastle->getY(), $this->_color, $this->_player->getArmies(), $this->_game, $this->_handler);
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
        $this->_inCastle = false;
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
                $this->_army->setFortified(true);
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
            if ($this->_inCastle) {
                $this->_army->setFortified(true);
                $this->next();
                return;
            }
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
        if ($mapRuinId = $this->_fields->getField($this->_armyX, $this->_armyY)->getRuinId()) {
            if (!$this->_game->getRuins()->getRuin($mapRuinId)->getEmpty()) {
                $this->_l->log('PRZESZUKUJĘ RUINY');
                $mapRuin = $this->_game->getRuins()->getRuin($mapRuinId);

                $array = array('mapRuinId' => $mapRuin->getId(), 'ruinId' => $mapRuin->getType());

                if ($array['ruinId'] == 4) {
                    $mapRuin->search($this->_game, $this->_army, $heroId, $this->_playerId, $this->_handler);
                } else {
                    foreach ($this->_army->getHeroes()->getKeys() as $heroId) {
                        $hero = $this->_army->getHeroes()->getHero($heroId);
                        if (!$hero->hasMapRuinBonus($mapRuinId)) {
                            $hero->addMapRuinBonus($array, $this->_gameId, $this->_db);
                        }
                    }

                }
                $this->_onMyWayToSearchRuin = false;
                $this->next();
                return;
            }
        }

        $ptnr = new Cli_Model_PathToNearestRuin($this->_game, $this->_army);
        if ($ruinId = $ptnr->getRuinId()) {
            $this->_l->log('IDĘ DO RUIN');
            $this->_onMyWayToSearchRuin = true;
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
        $field = $this->_fields->getField($path->getDestinationX(), $path->getDestinationY());

        if (!$field->getArmies() && !$field->getCastleId()) {
            $this->_l->log('BRAK ARMI I BRAK ZAMKU');
            $this->_army->resetOldPath();
            $this->next();
            return;
        } else {
            $this->_l->log('ARMIES:');
            $this->_l->log($field->getArmies());
            $this->_l->log('CastleId:');
            $this->_l->log($field->getCastleId());
        }

        $current = array();

        foreach ($path->getCurrent() as $step) {
            $current[] = $step;
            if ($step['x'] == $this->_armyX && $step['y'] == $this->_armyY) {
                continue;
            }
            if ($fieldArmies = $this->_fields->getField($step['x'], $step['y'])->getArmies()) {
                foreach ($fieldArmies as $armyId => $color) {
                    if ($color == $this->_color) {
                        $this->_l->log('armyId=' . $armyId . 'NA ŚCIEŻCE JEST MOJA ARMIA - DOŁĄCZAM');

                        $path->setCurrent($current);
                        $this->_army->resetOldPath();
                        $this->move($path);
                        return;
                    }
                }
            }
            $enemies = new Cli_Model_Enemies($this->_game, $step['x'], $step['y'], $this->_color);
            if ($enemies->hasEnemies()) {
                $this->_l->log('NA ŚCIEŻCE JEST WRÓG - ATAKUJĘ');

                $path->setCurrent($current);
                $this->_army->resetOldPath();
                $this->move($path);
                return;
            }
        }

        if ($path->getDestinationX() == $path->getX() && $path->getDestinationY() == $path->getY()) {
            $this->_army->saveOldPath($path);
            $this->move($path);
        } else {
            $this->savePathAndMove($path);
        }
    }

    private function move(Cli_Model_Path $path = null)
    {
        if ($path && $path->exists()) {
            $this->_l->log('(armyId=' . $this->_army->getId() . ')IDĘ/WALCZĘ');
            $this->_army->move($this->_game, $path, $this->_handler);
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
            } elseif ($this->_onMyWayToSearchRuin) {
                $this->ruinBlock();
            } else {
                $this->findPath();
            }
        } else {
            $this->_l->log('NASTĘPNA TURA');
            new Cli_Model_NextTurn($this->_user, $this->_handler);
        }
    }
}