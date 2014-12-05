<?php

class Cli_Model_Army
{
    private $_id;
    private $_x;
    private $_y;
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

        $this->_color = $color;

        $this->_x = $army['x'];
        $this->_y = $army['y'];

        $this->_attackFlyModifier = new Cli_Model_BattleModifier();
        $this->_attackHeroModifier = new Cli_Model_BattleModifier();
        $this->_defenseHeroModifier = new Cli_Model_BattleModifier();

        $this->_heroes = new Cli_Model_Heroes();
        $this->_soldiers = new Cli_Model_Soldiers();
        $this->_ships = new Cli_Model_Soldiers();

        if (isset($army['fortified'])) {
            $this->_fortified = $army['fortified'];
        }
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

//    public function unitsHaveRange($fullPath)
//    {
//        $soldiersMovesLeft = array();
//        $heroesMovesLeft = array();
//
//        foreach ($this->_soldiers as $soldierId => $soldier) {
//            // ustawiam początkową ilość ruchów dla każdej jednostki
//            if (!isset($soldiersMovesLeft[$soldierId])) {
//                $soldiersMovesLeft[$soldierId] = $this->_units[$soldier['unitId']]['numberOfMoves'];
//                if ($soldier->getMovesLeft() <= 2) {
//                    $soldiersMovesLeft[$soldierId] += $soldier->getMovesLeft();
//                } else {
//                    $soldiersMovesLeft[$soldierId] += 2;
//                }
//            }
//
//            foreach ($fullPath as $step) {
//                // odejmuję
//                if ($step['tt'] == 'f') {
//                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesForest'];
//                } elseif ($step['tt'] == 's') {
//                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesSwamp'];
//                } elseif ($step['tt'] == 'm') {
//                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesHills'];
//                } else {
//                    if ($this->_units[$soldier['unitId']]['canFly']) {
//                        $soldiersMovesLeft[$soldierId] -= $this->_terrain[$step['tt']]['flying'];
//                    } elseif ($this->_units[$soldier['unitId']]['canSwim']) {
//                        $soldiersMovesLeft[$soldierId] -= $this->_terrain[$step['tt']]['swimming'];
//                    } else {
//                        $soldiersMovesLeft[$soldierId] -= $this->_terrain[$step['tt']]['walking'];
//
//                    }
//                }
//
//                if ($step['tt'] == 'E') {
//                    break;
//                }
//
//                if ($soldiersMovesLeft[$soldierId] <= 0) {
//                    break;
//                }
//            }
//        }
//
//        foreach ($this->_heroes as $heroId => $hero) {
//            if (!isset($heroesMovesLeft[$heroId])) {
//                $heroesMovesLeft[$heroId] = $hero['numberOfMoves'];
//                if ($hero->getMovesLeft() <= 2) {
//                    $heroesMovesLeft[$heroId] += $hero->getMovesLeft();
//                } elseif ($hero->getMovesLeft() > 2) {
//                    $heroesMovesLeft[$heroId] += 2;
//                }
//            }
//
//            foreach ($fullPath as $step) {
//                $heroesMovesLeft[$heroId] -= $this->_terrain[$step['tt']]['walking'];
//
//                if ($step['tt'] == 'E') {
//                    break;
//                }
//
//                if ($heroesMovesLeft[$heroId] <= 0) {
//                    break;
//                }
//            }
//        }
//
//
//        foreach ($soldiersMovesLeft as $s) {
//            if ($s >= 0) {
//                return true;
//            }
//        }
//
//        foreach ($heroesMovesLeft as $h) {
//            if ($h >= 0) {
//                return true;
//            }
//        }
//    }

    public function move(Cli_Model_Game $game, Cli_Model_Path $path, $playerColor,
                         Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $gameId = $game->getId();
        $fields = $game->getFields();
        $players = $game->getPlayers();
        $player = $players->getPlayer($playerColor);

        $joinIds = null;
        $battleResult = new Cli_Model_BattleResult();

        $enemies = new Cli_Model_Enemies($game, $fields, $players, $path, $playerColor);
        if ($enemies->hasEnemies()) {
            $battle = new Cli_Model_Battle($this, $enemies->get(), $game, $db, $battleResult);
            $battle->fight();
            $battleResult = $battle->getResult();
            if ($battleResult->getVictory()) {
                $this->saveMove($gameId, $path, $fields, $db);
            }
        } else {
            $this->saveMove($gameId, $path, $fields, $db);
            $joinIds = $player->getArmies()->joinAtPosition($this->_id, $gameId, $db);
        }

        new Cli_Model_TowerHandler($player->getId(), $path, $game, $db, $gameHandler);

        $token = array(
            'color' => $playerColor,
            'army' => $this->toArray(), // todo
            'path' => $path->getCurrent(),
            'battle' => $battleResult->toArray(),
            'deletedIds' => $joinIds,
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

        $this->_movesLeft = $this->_heroes->saveMove($this->_x, $this->_y, $this->_movesLeft, $type, $path, $gameId, $db);
        $this->_movesLeft = $this->_soldiers->saveMove($this->_x, $this->_y, $this->_movesLeft, $type, $path, $gameId, $db);

        $this->_x = $path->getX();
        $this->_y = $path->getY();

        $mArmy = new Application_Model_Army($gameId, $db);
        $mArmy->updateArmyPosition($path->getEnd(), $this->_id);
    }

    public function getX()
    {
        return $this->_x;
    }

    public function getY()
    {
        return $this->_y;
    }

    public function getId()
    {
        return $this->_id;
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

    public function setAttackBattleSequence($attackBattleSequence)
    {
        $this->_attackBattleSequence['soldiers'] = $this->_soldiers->setAttackBattleSequence($attackBattleSequence);
        $this->_attackBattleSequence['heroes'] = $this->_heroes->get();
        $this->_attackBattleSequence['ships'] = $this->_ships->get();
    }

    public function setDefenceBattleSequence($defenceBattleSequence)
    {
        $this->_defenceBattleSequence['soldiers'] = $this->_soldiers->setDefenceBattleSequence($defenceBattleSequence);
        $this->_defenceBattleSequence['ships'] = $this->_ships->get();
        $this->_defenceBattleSequence['heroes'] = $this->_heroes->get();
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

    public function getCosts()
    {
        return $this->_soldiers->getCosts() + $this->_ships->getCosts();
    }

    public function createSoldier($gameId, $playerId, $unitId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $units = Zend_Registry::get('units');
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $soldierId = $mSoldier->add($this->_id, $unitId);

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

    public function setSoldiers($soldiers)
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
        foreach ($soldiers as $soldierId => $soldier) {
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

    public function addHeroes($heroes)
    {
        $numberOfHeroes = 0;
        foreach ($heroes as $heroId => $hero) {
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

    public function setDestroyed($destroyed)
    {
        $this->_destroyed = $destroyed;
    }
}
