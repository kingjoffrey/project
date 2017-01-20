<?php

class Cli_Model_Army
{
    private $_id;
    private $_color;
    private $_x;
    private $_y;
    private $_fortified = false;
    private $_destroyed = false;

    private $_defenseHeroModifier;

    private $_attackBattleSequence = array(
        'walk' => array(),
        'swim' => array(),
        'fly' => array(),
        'heroes' => array()
    );
    private $_defenceBattleSequence = array(
        'walk' => array(),
        'swim' => array(),
        'fly' => array(),
        'heroes' => array()
    );

    private $_Heroes;
    private $_WalkingSoldiers;
    private $_SwimmingSoldiers;
    private $_FlyingSoldiers;

    private $_movesLeft = 1000;

    private $_oldPath = array();

    /**
     * @param array $army
     * @param $color
     * @throws Exception
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

        $this->_defenseHeroModifier = new Cli_Model_BattleModifier();

        $this->_Heroes = new Cli_Model_Heroes();
        $this->_WalkingSoldiers = new Cli_Model_Soldiers();
        $this->_SwimmingSoldiers = new Cli_Model_Soldiers();
        $this->_FlyingSoldiers = new Cli_Model_Soldiers();

        if (isset($army['fortified'])) {
            $this->setFortified($army['fortified']);
        }
    }

    public function toArray()
    {
        return array(
            'id' => $this->_id,
            'walk' => $this->_WalkingSoldiers->toArray(),
            'swim' => $this->_SwimmingSoldiers->toArray(),
            'fly' => $this->_FlyingSoldiers->toArray(),
            'heroes' => $this->_Heroes->toArray(),
            'x' => $this->_x,
            'y' => $this->_y,
            'fortified' => $this->_fortified
        );
    }

    public function canSwim()
    {
        return $this->_SwimmingSoldiers->exists();
    }

    public function canFly()
    {
        if ($this->canSwim() || $this->_WalkingSoldiers->exists()) {
            return false;
        } elseif ($this->_FlyingSoldiers->count() >= $this->_Heroes->count()) {
            return true;
        } else {
            return false;
        }
    }

    public function move(Cli_Model_Game $game, Cli_Model_Path $path, $handler)
    {
        if (!$path->exists()) {
            echo 'PATH NOT EXISTS' . "\n";
            $token = array(
                'type' => 'move',
                'color' => $this->_color
            );

            $handler->sendToChannel($token);
            return;
        }

        $end = false;

        $db = $handler->getDb();

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
                $this->updateArmyPosition($game, $path, $db);
//                if ($battleResult->getCastleId() && $game->playerHasMoreThanFiftyPercentOfCastles($this->_color)) {
//                    $end = true;
//                }
            } else {
                $game->getFields()->getField($this->_x, $this->_y)->removeArmy($this->_id);
            }
        } else {
            $this->updateArmyPosition($game, $path, $db);
            $joinIds = $player->getArmies()->joinAtPosition($this->_id, $game, $db);
        }

        new Cli_Model_TowerHandler($player->getId(), $path, $game, $handler);

        $token = array(
            'color' => $this->_color,
            'army' => $this->toArray(),
            'path' => $path->getCurrent(),
            'battle' => $battleResult->toArray(),
            'deletedIds' => $joinIds,
            'type' => 'move'
        );

        $handler->sendToChannel($token);

//        if ($end) {
//            new Cli_Model_SaveResults($game, $handler);
//        }
    }

    public function getMovementType()
    {
        if ($this->canFly()) {
            return 'fly';
        } elseif ($this->canSwim()) {
            return 'swim';
        } else {
            return 'walk';
        }
    }

    private function updateArmyPosition(Cli_Model_Game $game, Cli_Model_Path $path, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $gameId = $game->getId();
        $terrain = $game->getTerrain();
        $type = $this->getMovementType();
        $this->setMovesLeft($this->_Heroes->saveMove($this->_x, $this->_y, $this->_movesLeft, $type, $path, $terrain, $gameId, $db));
        $this->setMovesLeft($this->_SwimmingSoldiers->saveMove($this->_x, $this->_y, $this->_movesLeft, $type, $path, $terrain, $gameId, $db));
        $this->setMovesLeft($this->_WalkingSoldiers->saveMove($this->_x, $this->_y, $this->_movesLeft, $type, $path, $terrain, $gameId, $db));
        $this->setMovesLeft($this->_FlyingSoldiers->saveMove($this->_x, $this->_y, $this->_movesLeft, $type, $path, $terrain, $gameId, $db));

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
        return $this->_Heroes;
    }

    public function getWalkingSoldiers()
    {
        return $this->_WalkingSoldiers;
    }

    public function getSwimmingSoldiers()
    {
        return $this->_SwimmingSoldiers;
    }

    public function getFlyingSoldiers()
    {
        return $this->_FlyingSoldiers;
    }

    public function resetMovesLeft($gameId = null, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_Heroes->resetMovesLeft($gameId, $db);
            $this->_WalkingSoldiers->resetMovesLeft($gameId, $db);
            $this->_SwimmingSoldiers->resetMovesLeft($gameId, $db);
            $this->_FlyingSoldiers->resetMovesLeft($gameId, $db);
        }

        $this->setMovesLeft(1000);

        foreach ($this->_Heroes->getKeys() as $heroId) {
            $hero = $this->_Heroes->getHero($heroId);
            if ($this->_movesLeft > $hero->getMovesLeft()) {
                $this->setMovesLeft($hero->getMovesLeft());
            }
        }
        foreach ($this->_WalkingSoldiers->getKeys() as $soldierId) {
            $soldier = $this->_WalkingSoldiers->getSoldier($soldierId);
            if ($this->_movesLeft > $soldier->getMovesLeft()) {
                $this->setMovesLeft($soldier->getMovesLeft());
            }
        }
        foreach ($this->_SwimmingSoldiers->getKeys() as $soldierId) {
            $soldier = $this->_SwimmingSoldiers->getSoldier($soldierId);
            if ($this->_movesLeft > $soldier->getMovesLeft()) {
                $this->setMovesLeft($soldier->getMovesLeft());
            }
        }
        foreach ($this->_FlyingSoldiers->getKeys() as $soldierId) {
            $soldier = $this->_FlyingSoldiers->getSoldier($soldierId);
            if ($this->_movesLeft > $soldier->getMovesLeft()) {
                $this->setMovesLeft($soldier->getMovesLeft());
            }
        }
    }

    public function setFortified($fortified, $gameId = null, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_fortified = $fortified;
//        echo '(armyId=' . $this->getId() . ')FORTIFY VALUE = ' . $this->_fortified . "\n";
        if ($db) {
            $mArmy = new Application_Model_Army($gameId, $db);
            $mArmy->fortify($this->getId(), $fortified);
        }
    }

    public function getFortified()
    {
        return $this->_fortified;
    }

    public function setAttackBattleSequence($attackBattleSequence)
    {
        $this->_attackBattleSequence['walk'] = $this->_WalkingSoldiers->setAttackBattleSequence($attackBattleSequence);
        $this->_attackBattleSequence['heroes'] = $this->_Heroes->get();
        $this->_attackBattleSequence['fly'] = $this->_FlyingSoldiers->get();
        $this->_attackBattleSequence['swim'] = $this->_SwimmingSoldiers->get();
    }

    public function setDefenceBattleSequence($defenceBattleSequence)
    {
        $this->_defenceBattleSequence['walk'] = $this->_WalkingSoldiers->setDefenceBattleSequence($defenceBattleSequence);
        $this->_defenceBattleSequence['heroes'] = $this->_Heroes->get();
        $this->_defenceBattleSequence['fly'] = $this->_FlyingSoldiers->get();
        $this->_defenceBattleSequence['swim'] = $this->_SwimmingSoldiers->get();
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
        $mod = 0;
        if ($this->_Heroes->exists()) {
            $mod++;
        }
        if ($this->_FlyingSoldiers->exists()) {
            $mod++;
        }
        return $mod;
    }

    public function getDefenseModifier()
    {
        $mod = 0;
        if ($this->_Heroes->exists()) {
            $mod++;
        }
        if ($this->_SwimmingSoldiers->exists()) {
            $mod++;
        }
        return $mod;
    }

    public function getCosts()
    {
        return $this->_WalkingSoldiers->getCosts() + $this->_SwimmingSoldiers->getCosts() + $this->_FlyingSoldiers->getCosts();
    }

    public function createSoldier($gameId, $playerId, $unitId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $units = Zend_Registry::get('units');
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $unit = $units->getUnit($unitId);
        $soldierId = $mSoldier->add($this->_id, $unitId, $unit->getNumberOfMoves());

        $soldier = new Cli_Model_Soldier(array('unitId' => $unitId, 'soldierId' => $soldierId), $unit);
        if ($unit->canSwim()) {
            $this->_SwimmingSoldiers->add($soldierId, $soldier);
        } elseif ($unit->canFly()) {
            $this->_FlyingSoldiers->add($soldierId, $soldier);
        } else {
            $this->_WalkingSoldiers->add($soldierId, $soldier);
        }

        if ($this->_movesLeft > $soldier->getMovesLeft()) {
            $this->setMovesLeft($soldier->getMovesLeft());
        }

        $mSoldiersCreated = new Application_Model_SoldiersCreated($gameId, $db);
        $mSoldiersCreated->add($unitId, $playerId);
    }

    private function addSoldier($soldierId, Cli_Model_Soldier $soldier, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mSoldier->soldierUpdateArmyId($soldierId, $this->_id);

        if ($this->_movesLeft > $soldier->getMovesLeft()) {
            $this->setMovesLeft($soldier->getMovesLeft());
        }
    }

    public function addWalkingSoldier($soldierId, $soldier, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_WalkingSoldiers->add($soldierId, $soldier);
        $this->addSoldier($soldierId, $soldier, $gameId, $db);
    }

    public function addSwimmingSoldier($soldierId, $soldier, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_SwimmingSoldiers->add($soldierId, $soldier);
        $this->addSoldier($soldierId, $soldier, $gameId, $db);
    }

    public function addFlyingSoldiers($soldierId, $soldier, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_FlyingSoldiers->add($soldierId, $soldier);
        $this->addSoldier($soldierId, $soldier, $gameId, $db);
    }

    public function addHero($heroId, $hero, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_Heroes->add($heroId, $hero);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->addToArmy($this->_id, $heroId, 0);

        if ($this->_movesLeft > $hero->getMovesLeft()) {
            $this->setMovesLeft($hero->getMovesLeft());
        }
    }

    public function initSoldiers($soldiers)
    {
        $units = Zend_Registry::get('units');
        foreach ($soldiers as $soldier) {
            $unit = $units->getUnit($soldier['unitId']);
            if ($unit->canSwim()) {
                $this->_SwimmingSoldiers->add($soldier['soldierId'], new Cli_Model_Soldier($soldier, $unit));
                $soldier = $this->_SwimmingSoldiers->getSoldier($soldier['soldierId']);
            } elseif ($unit->canFly()) {
                $this->_FlyingSoldiers->add($soldier['soldierId'], new Cli_Model_Soldier($soldier, $unit));
                $soldier = $this->_FlyingSoldiers->getSoldier($soldier['soldierId']);
            } else {
                $this->_WalkingSoldiers->add($soldier['soldierId'], new Cli_Model_Soldier($soldier, $unit));
                $soldier = $this->_WalkingSoldiers->getSoldier($soldier['soldierId']);
            }

            if ($this->_movesLeft > $soldier->getMovesLeft()) {
                $this->setMovesLeft($soldier->getMovesLeft());
            }
        }
    }

    public function initHeroes($heroes)
    {
        foreach ($heroes as $hero) {
            $this->_Heroes->add($hero['heroId'], new Cli_Model_Hero($hero));
            $hero = $this->_Heroes->getHero($hero['heroId']);

            if ($this->_movesLeft > $hero->getMovesLeft()) {
                $this->setMovesLeft($hero->getMovesLeft());
            }
        }
    }

    public function joinWalkingSoldiers(Cli_Model_Soldiers $soldiers)
    {
        foreach ($soldiers->getKeys() as $soldierId) {
            $soldier = $soldiers->getSoldier($soldierId);
            if ($this->_movesLeft > $soldier->getMovesLeft()) {
                $this->setMovesLeft($soldier->getMovesLeft());
            }
            $this->_WalkingSoldiers->add($soldierId, $soldier);
        }
    }

    public function joinSwimmingSoldiers(Cli_Model_Soldiers $soldiers)
    {
        foreach ($soldiers->getKeys() as $soldierId) {
            $soldier = $soldiers->getSoldier($soldierId);
            if ($this->_movesLeft > $soldier->getMovesLeft()) {
                $this->setMovesLeft($soldier->getMovesLeft());
            }
            $this->_SwimmingSoldiers->add($soldierId, $soldier);
        }
    }

    public function joinFlyingSoldiers(Cli_Model_Soldiers $soldiers)
    {
        foreach ($soldiers->getKeys() as $soldierId) {
            $soldier = $soldiers->getSoldier($soldierId);
            if ($this->_movesLeft > $soldier->getMovesLeft()) {
                $this->setMovesLeft($soldier->getMovesLeft());
            }
            $this->_FlyingSoldiers->add($soldierId, $soldier);
        }
    }

    public function joinHeroes(Cli_Model_Heroes $heroes)
    {
        foreach ($heroes->getKeys() as $heroId) {
            $hero = $heroes->getHero($heroId);
            if ($this->_movesLeft > $hero->getMovesLeft()) {
                $this->setMovesLeft($hero->getMovesLeft());
            }
            $this->_Heroes->add($heroId, $hero);
        }
    }

    public function removeHero($heroId, $winnerId, $loserId, $gameId, $db)
    {
        $hero = $this->_Heroes->getHero($heroId);
        $hero->death($gameId, $db, $winnerId, $loserId);
        $this->_Heroes->remove($heroId);
    }

    public function removeWalkingSoldier($soldierId, $winnerId, $loserId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $soldier = $this->_WalkingSoldiers->getSoldier($soldierId);
        $soldier->death($gameId, $db, $winnerId, $loserId);
        $this->_WalkingSoldiers->remove($soldierId);
    }

    public function removeSwimmingSoldier($soldierId, $winnerId, $loserId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $soldier = $this->_SwimmingSoldiers->getSoldier($soldierId);
        $soldier->death($gameId, $db, $winnerId, $loserId);
        $this->_SwimmingSoldiers->remove($soldierId);
    }

    public function removeFlyingSoldier($soldierId, $winnerId, $loserId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $soldier = $this->_FlyingSoldiers->getSoldier($soldierId);
        $soldier->death($gameId, $db, $winnerId, $loserId);
        $this->_FlyingSoldiers->remove($soldierId);
    }

    public function resetAttributes()
    {
        $this->setMovesLeft(1000);

        foreach ($this->_Heroes->getKeys() as $heroId) {
            $hero = $this->_Heroes->getHero($heroId);
            if ($this->_movesLeft > $hero->getMovesLeft()) {
                $this->setMovesLeft($hero->getMovesLeft());
            }
        }

        foreach ($this->_WalkingSoldiers->getKeys() as $soldierId) {
            $soldier = $this->_WalkingSoldiers->getSoldier($soldierId);
            if ($this->_movesLeft > $soldier->getMovesLeft()) {
                $this->setMovesLeft($soldier->getMovesLeft());
            }
        }
        foreach ($this->_SwimmingSoldiers->getKeys() as $soldierId) {
            $soldier = $this->_SwimmingSoldiers->getSoldier($soldierId);
            if ($this->_movesLeft > $soldier->getMovesLeft()) {
                $this->setMovesLeft($soldier->getMovesLeft());
            }
        }
        foreach ($this->_FlyingSoldiers->getKeys() as $soldierId) {
            $soldier = $this->_FlyingSoldiers->getSoldier($soldierId);
            if ($this->_movesLeft > $soldier->getMovesLeft()) {
                $this->setMovesLeft($soldier->getMovesLeft());
            }
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

    public function getDestroyed()
    {
        return $this->_destroyed;
    }

    public function count()
    {
        return $this->_WalkingSoldiers->count() + $this->_SwimmingSoldiers->count() + $this->_FlyingSoldiers->count() + $this->_Heroes->count();
    }

    public function saveOldPath(Cli_Model_Path $path)
    {
        $start = false;
        $this->resetOldPath();
        if (!is_array($path->getFull())) {
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('brak full path');
        }
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

    private function setMovesLeft($movesLeft)
    {
//        echo "\n";
//        echo 'armyId=' . $this->_id . ' setMovesLeft($movesLeft)=' . $movesLeft . ' BEFORE: ' . $this->_movesLeft . "\n";
        if ($movesLeft === null) {
            throw new Exception('wtf!');
        }
        $this->_movesLeft = $movesLeft;
    }
}
