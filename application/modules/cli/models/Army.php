<?php

class Cli_Model_Army
{
    private $_id;
    private $_color;
    private $_x;
    private $_y;
    private $_fortified = false;
    private $_destroyed = false;

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

    private $_oldPath = array();

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
            'id' => $this->_id,
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

    public function move(Cli_Model_Game $game, Cli_Model_Path $path, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        $gameId = $game->getId();

        if (!$path->exists()) {
            $token = array(
                'type' => 'move'
            );

            $gameHandler->sendToChannel($db, $token, $gameId);
            return;
        }

        $players = $game->getPlayers();
        $player = $players->getPlayer($this->_color);

        $joinIds = null;
        $battleResult = new Cli_Model_BattleResult();

        $enemies = new Cli_Model_Enemies($game, $path->getX(), $path->getY(), $this->_color);
        if ($enemies->hasEnemies()) {
            $battle = new Cli_Model_Battle($this, $enemies, $game, $db, $battleResult);
            $battle->fight();
            $battleResult = $battle->getResult();
            if ($battleResult->getVictory()) {
                $this->saveMove($game, $path, $db);
            }
        } else {
            $this->saveMove($game, $path, $db);
            $joinIds = $player->getArmies()->joinAtPosition($this->_id, $game, $db);
        }

        new Cli_Model_TowerHandler($player->getId(), $path, $game, $db, $gameHandler);

        $token = array(
            'color' => $this->_color,
            'army' => $this->toArray(),
            'path' => $path->getCurrent(),
            'battle' => $battleResult->toArray(),
            'deletedIds' => $joinIds,
            'type' => 'move'
        );

        $gameHandler->sendToChannel($db, $token, $gameId);
    }

    private function saveMove(Cli_Model_Game $game, Cli_Model_Path $path, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        if ($this->canFly()) {
            $type = 'flying';
        } elseif ($this->canSwim()) {
            $type = 'swimming';
        } else {
            $type = 'walking';
        }
        $gameId = $game->getId();
        $this->_movesLeft = $this->_heroes->saveMove($this->_x, $this->_y, $this->_movesLeft, $type, $path, $gameId, $db);
        $this->_movesLeft = $this->_soldiers->saveMove($this->_x, $this->_y, $this->_movesLeft, $type, $path, $gameId, $db);

        $game->getFields()->getField($this->_x, $this->_y)->removeArmy($this->_id);
        $this->_x = $path->getX();
        $this->_y = $path->getY();
        $game->getFields()->getField($this->_x, $this->_y)->addArmy($this->_id, $this->_color);

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
        $this->_heroes->resetMovesLeft($gameId, $db);
        $this->_soldiers->resetMovesLeft($gameId, $db);
        $this->_ships->resetMovesLeft($gameId, $db);
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

        $soldier = new Cli_Model_Soldier(array('unitId' => $unitId, 'soldierId' => $soldierId), $units[$unitId]);
        $this->_soldiers->add($soldierId, $soldier);

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

    public function addSoldier($soldierId, $soldier, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_soldiers->add($soldierId, $soldier);
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mSoldier->soldierUpdateArmyId($soldierId, $this->_id);

        if ($soldier->canFly()) {
            $this->_attackFlyModifier->increment();
            $this->_canFly++;
        } else {
            $this->_canFly--;
        }

        if ($this->_movesLeft > $soldier->getMovesLeft()) {
            $this->_movesLeft = $soldier->getMovesLeft();
        }
    }

    public function addShip($soldierId, $soldier, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_ships->add($soldierId, $soldier);
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mSoldier->soldierUpdateArmyId($soldierId, $this->_id);

        if ($this->_movesLeft > $soldier->getMovesLeft()) {
            $this->_movesLeft = $soldier->getMovesLeft();
        }
    }

    public function addHero($heroId, $hero, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_heroes->add($heroId, $hero);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->addToArmy($this->_id, $heroId, 0);

        if ($this->_movesLeft > $hero->getMovesLeft()) {
            $this->_movesLeft = $hero->getMovesLeft();
        }
        $this->_attackHeroModifier->increment();
        $this->_defenseHeroModifier->increment();
    }

    public function initSoldiers($soldiers)
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

    public function initHeroes($heroes)
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

    public function joinSoldiers(Cli_Model_Soldiers $soldiers)
    {
        foreach ($soldiers->getKeys() as $soldierId) {
            $soldier = $soldiers->getSoldier($soldierId);
            if ($soldier->canFly()) {
                $this->_attackFlyModifier->increment();
                $this->_canFly++;
            } else {
                $this->_canFly--;
            }
            $this->_soldiers->add($soldierId, $soldier);
        }
    }

    public function joinShips(Cli_Model_Soldiers $soldiers)
    {
        foreach ($soldiers->getKeys() as $soldierId) {
            $this->_ships->add($soldierId, $soldiers->getSoldier($soldierId));
        }
    }

    public function joinHeroes(Cli_Model_Heroes $heroes)
    {
        $numberOfHeroes = 0;
        foreach ($heroes->getKeys() as $heroId) {
            $hero = $heroes->getHero($heroId);
            if ($this->_movesLeft > $hero->getMovesLeft()) {
                $this->_movesLeft = $hero->getMovesLeft();
            }
            $this->_heroes->add($heroId, $hero);
            $this->_attackHeroModifier->increment();
            $this->_defenseHeroModifier->increment();
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

    public function count()
    {
        return $this->_soldiers->count() + $this->_ships->count() + $this->_heroes->count();
    }

    public function saveOldPath(Cli_Model_Path $path)
    {
        $start = false;

        foreach ($path->getFull() as $step) {
            if ($path->getX() == $step['x'] && $path->getY() == $step['y']) {
                $start = true;
            }

            if ($start) {
                $this->_oldPath[] = $step;
            }
        }
    }

    public function resetOldPath()
    {
        $this->_oldPath = array();
    }

    public function hasOldPath()
    {
        if ($this->_oldPath) {
            return true;
        }
    }

    public function getOldPath()
    {
        return $this->_oldPath;
    }
}
