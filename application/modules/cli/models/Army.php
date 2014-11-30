<?php

class Cli_Model_Army extends Cli_Model_Entity
{
    private $_fortified = false;
    private $_destroyed = false;

    private $_color;

    private $_attackHeroModifier;
    private $_attackFlyModifier;
    private $_defenseHeroModifier;

    private $_attackBattleSequence = array(
        'soldiers' => array(),
        'ships' => array(),
        'heroes' => array()
    );
    private $_defenceBattleSequence = array(
        'soldiers' => array(),
        'ships' => array(),
        'heroes' => array()
    );

    private $_heroes = array();
    private $_soldiers = array();
    private $_ships = array();

    private $_canFly = 0;

    private $_movesLeft = 1000;

    /*
     * @param array $army
     */
    public function __construct(array $army, $color)
    {
        if (!isset($army['armyId'])) {
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('no "armyId"');
        }
        $this->_id = $army['armyId'];

        if (!isset($army['x']) || !isset($army['y'])) {
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('no "x" or "y"');
        }

        if (empty($color)) {
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('no "color"');
        }

        $this->_color = $color;

        $this->_x = $army['x'];
        $this->_y = $army['y'];

        $this->_attackFlyModifier = new Cli_Model_BattleModifier();
        $this->_attackHeroModifier = new Cli_Model_BattleModifier();
        $this->_defenseHeroModifier = new Cli_Model_BattleModifier();

        $this->_heroes = new Cli_Model_Heroes();
        $this->_soldiers = new Cli_Model_Soldiers();
        $this->_ships = new Cli_Model_Soldiers();
    }

    public function toArray()
    {
        return array(
            'armyId' => $this->_id,
            'soldiers' => $this->_soldiers->toArray(),
            'ships' => $this->_ships->toArray(),
            'heroes' => $this->_heroes->toArray(),
            'x' => $this->_x,
            'y' => $this->_y,
            'fortified' => $this->_fortified,
            'destroyed' => $this->_destroyed,
            'canFly' => $this->canFly(),
            'canSwim' => $this->canSwim(),
            'movesLeft' => $this->_movesLeft
        );
    }

    public function canSwim()
    {
        return $this->_ships->exists();
    }

    public function canFly()
    {
        if ($this->_canFly > 0) {
            return true;
        }
    }

    public function unitsHaveRange($fullPath)
    {
        $soldiersMovesLeft = array();
        $heroesMovesLeft = array();

        foreach ($this->_soldiers as $soldierId => $soldier) {
            // ustawiam początkową ilość ruchów dla każdej jednostki
            if (!isset($soldiersMovesLeft[$soldierId])) {
                $soldiersMovesLeft[$soldierId] = $this->_units[$soldier['unitId']]['numberOfMoves'];
                if ($soldier->getMovesLeft() <= 2) {
                    $soldiersMovesLeft[$soldierId] += $soldier->getMovesLeft();
                } else {
                    $soldiersMovesLeft[$soldierId] += 2;
                }
            }

            foreach ($fullPath as $step) {
                // odejmuję
                if ($step['tt'] == 'f') {
                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesForest'];
                } elseif ($step['tt'] == 's') {
                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesSwamp'];
                } elseif ($step['tt'] == 'm') {
                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesHills'];
                } else {
                    if ($this->_units[$soldier['unitId']]['canFly']) {
                        $soldiersMovesLeft[$soldierId] -= $this->_terrain[$step['tt']]['flying'];
                    } elseif ($this->_units[$soldier['unitId']]['canSwim']) {
                        $soldiersMovesLeft[$soldierId] -= $this->_terrain[$step['tt']]['swimming'];
                    } else {
                        $soldiersMovesLeft[$soldierId] -= $this->_terrain[$step['tt']]['walking'];

                    }
                }

                if ($step['tt'] == 'E') {
                    break;
                }

                if ($soldiersMovesLeft[$soldierId] <= 0) {
                    break;
                }
            }
        }

        foreach ($this->_heroes as $heroId => $hero) {
            if (!isset($heroesMovesLeft[$heroId])) {
                $heroesMovesLeft[$heroId] = $hero['numberOfMoves'];
                if ($hero->getMovesLeft() <= 2) {
                    $heroesMovesLeft[$heroId] += $hero->getMovesLeft();
                } elseif ($hero->getMovesLeft() > 2) {
                    $heroesMovesLeft[$heroId] += 2;
                }
            }

            foreach ($fullPath as $step) {
                $heroesMovesLeft[$heroId] -= $this->_terrain[$step['tt']]['walking'];

                if ($step['tt'] == 'E') {
                    break;
                }

                if ($heroesMovesLeft[$heroId] <= 0) {
                    break;
                }
            }
        }


        foreach ($soldiersMovesLeft as $s) {
            if ($s >= 0) {
                return true;
            }
        }

        foreach ($heroesMovesLeft as $h) {
            if ($h >= 0) {
                return true;
            }
        }
    }

    public function move(Cli_Model_Game $game, Cli_Model_Path $path, $playerColor,
                         Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler, $ruinId = null)
    {
        $gameId = $game->getId();
        $fields = $game->getFields();
        $players = $game->getPlayers();

        $joinIds = array();
        $battleResult = array();

        $enemies = new Cli_Model_Enemies($game, $fields, $players, $path, $playerColor);
        if ($enemies->hasEnemies()) {
            $battle = new Cli_Model_Battle($this, $enemies->get(), $game, true);
            $battle->fight();
            $battle->saveResult($game, $db);
            $battleResult = $battle->getResult();
            if ($battle->attackerVictory()) {
                $this->saveMove($gameId, $path, $fields, $db);
            }
        } else {
            $this->saveMove($gameId, $path, $fields, $db);
            $joinIds = $players->getPlayer($playerColor)->getArmies()->joinAtPosition($this->_id, $gameId, $db);
        }

        $token = array(
            'color' => $playerColor,
            'army' => $this->toArray(),
            'path' => $path->getCurrent(),
            'defendersIds' => $enemies->toArray(),
            'battle' => $battleResult,
            'deletedIds' => $joinIds,
            'ruinId' => $ruinId,
            'type' => 'move'
        );

        $gameHandler->sendToChannel($db, $token, $gameId);
    }

    private function saveMove($gameId, Cli_Model_Path $path, Cli_Model_Fields $fields, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        if ($this->canFly()) {
            $type = 'flying';
        } elseif ($this->canSwim()) {
            $type = 'swimming';
        } else {
            $type = 'walking';
        }

        $currentPath = $path->getCurrent();

        if (count($this->_heroes)) {
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

            foreach ($this->_heroes as $heroId => $hero) {
                $movesSpend = 0;

                foreach ($currentPath as $step) {
                    if ($step['x'] == $this->_x && $step['y'] == $this->_y) {
                        break;
                    }
                    if (!isset($step['myCastleCosts'])) {
                        $movesSpend += $this->_terrain[$fields->getType($step['x'], $step['y'])][$type];
                    }
                }

                $hero->updateMovesLeft($heroId, $movesSpend, $mHeroesInGame);

                if ($this->_movesLeft > $hero->getMovesLeft()) {
                    $this->setMovesLeft($hero->getMovesLeft());
                }
            }
        }

        if (count($this->_soldiers)) {
            $mSoldier = new Application_Model_UnitsInGame($gameId, $db);

            if ($this->canFly() || $this->canSwim()) {
                foreach ($this->_soldiers as $soldierId => $soldier) {
                    $movesSpend = 0;

                    foreach ($currentPath as $step) {
                        if ($step['x'] == $this->_x && $step['y'] == $this->_y) {
                            break;
                        }
                        if (!isset($step['myCastleCosts'])) {
                            $movesSpend += $this->_terrain[$fields->getType($step['x'], $step['y'])][$type];
                        }
                    }

                    $soldier->updateMovesLeft($soldierId, $movesSpend, $mSoldier);

                    if ($this->_movesLeft > $soldier->getMovesLeft()) {
                        $this->setMovesLeft($soldier->getMovesLeft());
                    }
                }
            } else {
                foreach ($this->_soldiers as $soldierId => $soldier) {
                    $movesSpend = 0;

                    $this->_terrain['f'][$type] = $soldier->getForest();
                    $this->_terrain['m'][$type] = $soldier->getHills();
                    $this->_terrain['s'][$type] = $soldier->getSwamp();

                    foreach ($currentPath as $step) {
                        if ($step['x'] == $this->_x && $step['y'] == $this->_y) {
                            break;
                        }
                        if (!isset($step['myCastleCosts'])) {
                            $movesSpend += $this->_terrain[$fields->getType($step['x'], $step['y'])][$type];
                        }
                    }

                    $soldier->updateMovesLeft($soldierId, $movesSpend, $mSoldier);

                    if ($this->_movesLeft > $soldier->getMovesLeft()) {
                        $this->setMovesLeft($soldier->getMovesLeft());
                    }
                }
            }
        }

        $mArmy = new Application_Model_Army($gameId, $db);

        $this->_x = $path->getX();
        $this->_y = $path->getY();

        return $mArmy->updateArmyPosition($path->getEnd(), $this->_id);
    }

    public function getMovesLeft()
    {
        return $this->_movesLeft;
    }

    public function getHeroes()
    {
        return $this->_heroes;
    }

    public function getSoldiers()
    {
        return $this->_soldiers;
    }

    public function getShips()
    {
        return $this->_ships;
    }

    public function resetMovesLeft($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        foreach ($this->_heroes as $hero) {
            $hero->resetMovesLeft($gameId, $db);
        }
        foreach ($this->_soldiers as $soldier) {
            $soldier->resetMovesLeft($gameId, $db);
        }
    }

    public function setFortified($fortified, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_fortified = $fortified;
        $mArmy = new Application_Model_Army($gameId, $db);
        $mArmy->fortify($this->getId(), $fortified);
    }

    public function getFortified()
    {
        return $this->_fortified;
    }

    public function zeroHeroMovesLeft($heroId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_heroes->getHero($heroId)->zeroMovesLeft($gameId, $db);
    }

    public function getNumberOfSoldiers()
    {
        return count($this->_soldiers);
    }

    public function getNumberOfHeroes()
    {
        return count($this->_heroes);
    }

    public function count()
    {
        return count($this->_soldiers) + count($this->_ships) + count($this->_heroes);
    }

    public function setAttackBattleSequence($attackBattleSequence)
    {
        $this->_attackBattleSequence['soldiers'] = $this->_soldiers->setAttackBattleSequence($attackBattleSequence);
        $this->_attackBattleSequence['heroes'] = $this->_heroes->toArray();
        $this->_attackBattleSequence['ships'] = $this->_ships->toArray();
    }

    public function setDefenceBattleSequence($defenceBattleSequence)
    {
        $this->_defenceBattleSequence['soldiers'] = $this->_soldiers->setDefenceBattleSequence($defenceBattleSequence);
        $this->_defenceBattleSequence['ships'] = $this->_ships->toArray();
        $this->_defenceBattleSequence['heroes'] = $this->_heroes->toArray();
    }

    public function getAttackBattleSequence()
    {
        return $this->_attackBattleSequence;
    }

    public function getDefenceBattleSequence()
    {
        return $this->_defenceBattleSequence;
    }

    public function getAttackModifier()
    {
        return $this->_attackHeroModifier->get() + $this->_attackFlyModifier->get();
    }

    public function getDefenseModifier()
    {
        return $this->_defenseHeroModifier->get();
    }

    public function attackerVictory()
    {
        return count($this->_attackBattleSequence['soldiers']) || count($this->_attackBattleSequence['heroes']) || count($this->_attackBattleSequence['ships']);
    }

    public function getCosts()
    {
        return $this->_soldiers->getCosts() + $this->_ships->getCosts();
    }

    public function createSoldier($gameId, $playerId, $armyId, $unitId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $units = Zend_Registry::get('units');
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $soldierId = $mSoldier->add($armyId, $unitId);

        $this->_soldiers->add($soldierId, new Cli_Model_Soldier(array('unitId' => $unitId, 'soldierId' => $soldierId), $units[$unitId]));
        $soldier = $this->_soldiers->getSoldier($soldierId);
        if ($soldier->canFly()) {
            $this->_attackFlyModifier->increment();
            $this->_canFly++;
        } else {
            $this->_canFly--;
        }

        if ($this->_movesLeft > $soldier->getMovesLeft()) {
            $this->_movesLeft = $soldier->getMovesLeft();
        }

        $mSoldiersCreated = new Application_Model_SoldiersCreated($gameId, $db);
        $mSoldiersCreated->add($unitId, $playerId);
    }

    public function createHero($gameId, $heroId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->addToArmy($this->_id, $heroId, 0);

        $this->_heroes->add($heroId, new Cli_Model_Hero($mHeroesInGame->getForMove($this->_id)));
        $hero = $this->_heroes->getHero($heroId);
        if ($this->_movesLeft > $hero->getMovesLeft()) {
            $this->_movesLeft = $hero->getMovesLeft();
        }
        $this->_attackHeroModifier->increment();
        $this->_defenseHeroModifier->increment();
    }

    public function setSoldiers(array $soldiers)
    {
        $units = Zend_Registry::get('units');
        foreach ($soldiers as $soldier) {
            $unit = $units[$soldier['unitId']];
            if ($unit['canSwim']) {
                $this->_ships->add($soldier['soldierId'], new Cli_Model_Soldier($soldier, $unit));
                $soldier = $this->_ships->getSoldier($soldier['soldierId']);
            } else {
                $this->_soldiers->add($soldier['soldierId'], new Cli_Model_Soldier($soldier, $unit));
                $soldier = $this->_soldiers->getSoldier($soldier['soldierId']);
            }
            if ($this->_movesLeft > $soldier->getMovesLeft()) {
                $this->_movesLeft = $soldier->getMovesLeft();
            }
            if ($soldier->canFly()) {
                $this->_attackFlyModifier->increment();
                $this->_canFly++;
            } else {
                $this->_canFly--;
            }
        }
    }

    public function setHeroes($heroes)
    {
        $numberOfHeroes = 0;
        foreach ($heroes as $hero) {
            $this->_heroes->add($hero['heroId'], new Cli_Model_Hero($hero));
            $hero = $this->_heroes->getHero($hero['heroId']);
            if ($this->_movesLeft > $hero->getMovesLeft()) {
                $this->_movesLeft = $hero->getMovesLeft();
            }
            $this->_attackHeroModifier->increment();
            $this->_defenseHeroModifier->increment();
            $numberOfHeroes++;
        }
        $this->_canFly += -$numberOfHeroes + 1; // ????? todo
    }

    public function addSoldiers(Cli_Model_Soldiers $soldiers)
    {
        foreach ($soldiers->get() as $soldierId => $soldier) {
            if ($soldier->canSwim()) {
                $this->_ships->add($soldierId, $soldier);
            } else {
                if ($soldier->canFly()) {
                    $this->_attackFlyModifier->increment();
                    $this->_canFly++;
                } else {
                    $this->_canFly--;
                }
                $this->_soldiers->add($soldierId, $soldier);
            }
        }
    }

    public function addHeroes(Cli_Model_Heroes $heroes)
    {
        $numberOfHeroes = 0;
        foreach ($heroes->get() as $heroId => $hero) {
            if ($this->_movesLeft > $hero->getMovesLeft()) {
                $this->_movesLeft = $hero->getMovesLeft();
            }
            $this->_heroes->add($heroId, $hero);
            $this->_attackModifier->increment();
            $this->_defenseModifier->increment();
            $numberOfHeroes++;
        }
        $this->_canFly += -$numberOfHeroes + 1; // ?????? todo
    }

    public function removeHero($heroId, $winnerId, $loserId, $gameId, $db)
    {
        $this->_heroes->getHero($heroId)->death($gameId, $db, $winnerId, $loserId);
        $this->_heroes->remove($heroId);
        $this->_attackHeroModifier->decrement();
        $this->_defenseHeroModifier->decrement();
    }

    public function removeSoldier($soldierId, $winnerId, $loserId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $soldier = $this->_soldiers->getSoldier($soldierId);
        $soldier->death($gameId, $db, $winnerId, $loserId);
        $this->_soldiers->remove($soldierId);
        if ($soldier->canFly()) {
            $this->_attackFlyModifier->decrement();
            $this->_canFly--;
        } else {
            $this->_canFly++;
        }
    }

    public function getColor()
    {
        return $this->_color;
    }
}
