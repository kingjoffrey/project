<?php

class Cli_Model_Armies
{
    private $_armies = array();

    public function getArray()
    {
        return $this->_armies;
    }

    public function getKeys()
    {
        return array_keys($this->_armies);
    }

    public function addArmy($armyId, $army)
    {
        $this->_armies[$armyId] = $army;
    }

    public function hasArmy($armyId)
    {
        return isset($this->_armies[$armyId]);
    }

    /**
     * @param $armyId
     * @return Cli_Model_Army
     */
    public function getArmy($armyId)
    {
        if (!$this->hasArmy($armyId)) {
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4));
            throw new Exception('No army with armyId=' . $armyId);
        }
        return $this->_armies[$armyId];
    }

    public function toArray()
    {
        $armies = array();
        foreach ($this->_armies as $armyId => $army) {
            $armies[$armyId] = $army->toArray();
        }
        return $armies;
    }

    /**
     * @return Cli_Model_Army
     */
    public function getComputerArmyToMove()
    {
        foreach ($this->getKeys() as $armyId) {
            $army = $this->getArmy($armyId);
            if ($army->getFortified() || $army->getMovesLeft() == 0) {
                continue;
            }
//            if ($army->getFortified()) {
//                echo '(armyId='.$armyId.')FORTIFIED' . "\n";
//                continue;
//            }
//            if ($army->getMovesLeft() == 0) {
//                echo '(armyId='.$armyId.')ZERO MOVES LEFT' . "\n";
//                continue;
//            }
            return $army;
        }
    }

    public function joinAtPosition($excludedArmyId, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $excludedArmy = $this->getArmy($excludedArmyId);
        $x = $excludedArmy->getX();
        $y = $excludedArmy->getY();
        $color = $excludedArmy->getColor();
        $gameId = $game->getId();

        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        $ids = array();

        foreach ($game->getFields()->getField($x, $y)->getArmies() as $armyId => $armyColor) {
            if ($armyId == $excludedArmyId) {
                continue;
            }
            if ($color == $armyColor) {
                $army = $this->getArmy($armyId);
                $excludedArmy->joinHeroes($army->getHeroes());
                $excludedArmy->joinWalkingSoldiers($army->getWalkingSoldiers());
                $excludedArmy->joinSwimmingSoldiers($army->getSwimmingSoldiers());
                $excludedArmy->joinFlyingSoldiers($army->getFlyingSoldiers());
                $mHeroesInGame->heroesUpdateArmyId($armyId, $excludedArmyId);
                $mSoldier->soldiersUpdateArmyId($armyId, $excludedArmyId);
                $this->removeArmy($armyId, $game, $db);
                $ids[] = $armyId;
            }
        }
        return $ids;
    }

    /**
     * @param $armyId
     * @param Cli_Model_Game $game
     * @param Zend_Db_Adapter_Pdo_Pgsql|null $db
     * @throws Exception
     */
    public function removeArmy($armyId, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $army = $this->getArmy($armyId);
        $army->setDestroyed(true);
        $game->getFields()->getField($army->getX(), $army->getY())->removeArmy($army->getId());
        if ($db) {
            $mArmy = new Application_Model_Army($game->getId(), $db);
            $mArmy->destroyArmy($armyId);
        }
        unset($this->_armies[$armyId]);
    }

    public function resetMovesLeft($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        foreach ($this->getKeys() as $armyId) {
            $this->getArmy($armyId)->resetMovesLeft($gameId, $db);
        }
    }

    public function getArmyIdFromField(Cli_Model_Field $field)
    {
        foreach ($field->getArmies() as $armyId => $color) {
            if (isset($this->_armies[$armyId])) {
                return $armyId;
            }
        }
    }

    public function unfortify()
    {
        foreach ($this->_armies as $army) {
            $army->setFortified(false);
        }
    }

    public function initFields(Cli_Model_Fields $fields, $color)
    {
        foreach ($this->_armies as $armyId => $army) {
            $fields->getField($army->getX(), $army->getY())->addArmy($armyId, $color);
        }
    }

    public function noArmiesExists()
    {
        return !count($this->_armies);
    }

    public function exists()
    {
        return count($this->_armies);
    }

    public function create($x, $y, $color, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mArmy = new Application_Model_Army($game->getId(), $db);
        $armyId = $mArmy->createArmy(array('x' => $x, 'y' => $y), $game->getPlayers()->getPlayer($color)->getId());
        $army = new Cli_Model_Army(array(
            'armyId' => $armyId,
            'x' => $x,
            'y' => $y
        ), $color);
        $this->addArmy($armyId, $army);
        $game->getFields()->getField($army->getX(), $army->getY())->addArmy($armyId, $color);
        return $armyId;
    }

    public function changeHeroAffiliation($oldArmyId, $newArmyId, $heroId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->getArmy($newArmyId)->addHero($heroId, $this->getArmy($oldArmyId)->getHeroes()->getHero($heroId), $gameId, $db);
        $this->getArmy($oldArmyId)->getHeroes()->remove($heroId);
    }

    public function changeWalkingSoldierAffiliation($oldArmyId, $newArmyId, $soldierId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->getArmy($newArmyId)->addWalkingSoldier($soldierId, $this->getArmy($oldArmyId)->getWalkingSoldiers()->getSoldier($soldierId), $gameId, $db);
        $this->getArmy($oldArmyId)->getWalkingSoldiers()->remove($soldierId);
    }

    public function changeSwimmingSoldierAffiliation($oldArmyId, $newArmyId, $soldierId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->getArmy($newArmyId)->addSwimmingSoldier($soldierId, $this->getArmy($oldArmyId)->getSwimmingSoldiers()->getSoldier($soldierId), $gameId, $db);
        $this->getArmy($oldArmyId)->getSwimmingSoldiers()->remove($soldierId);
    }

    public function changeFlyingSoldierAffiliation($oldArmyId, $newArmyId, $soldierId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->getArmy($newArmyId)->addFlyingSoldiers($soldierId, $this->getArmy($oldArmyId)->getFlyingSoldiers()->getSoldier($soldierId), $gameId, $db);
        $this->getArmy($oldArmyId)->getFlyingSoldiers()->remove($soldierId);
    }

    public function count()
    {
        return count($this->_armies);
    }
}