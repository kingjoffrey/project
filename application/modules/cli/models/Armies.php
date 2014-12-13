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

    /**
     * @param $armyId
     * @return Cli_Model_Army
     */
    public function getArmy($armyId)
    {
        if (!isset($this->_armies[$armyId])) {
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
        foreach ($this->_armies as $army) {
            if ($army->getFortified()) {
                continue;
            }
            return $army;
        }
    }

    public function joinAtPosition($excludedArmyId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $excludedArmy = $this->getArmy($excludedArmyId);
        $x = $excludedArmy->getX();
        $y = $excludedArmy->getY();

        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        $ids = array();

        foreach ($this->getKeys() as $armyId) {
            $army = $this->getArmy($armyId);
            if ($armyId == $excludedArmyId) {
                continue;
            }
            if ($x == $army->getX() && $y == $army->getY()) {
                $excludedArmy->joinHeroes($army->getHeroes(), $mHeroesInGame);
                $mHeroesInGame->heroesUpdateArmyId($armyId, $excludedArmyId);
                $excludedArmy->joinSoldiers($army->getSoldiers(), $mSoldier);
                $mSoldier->soldiersUpdateArmyId($armyId, $excludedArmyId);
                $this->removeArmy($armyId, $gameId, $db);
                $ids[] = $armyId;
            }
        }
        return $ids;
    }

    public function removeArmy($armyId, $gameId = null, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $mArmy = new Application_Model_Army($gameId, $db);
            $mArmy->destroyArmy($armyId);
            $this->getArmy($armyId)->setDestroyed(true);
        }
        unset($this->_armies[$armyId]);
    }

    public function resetMovesLeft($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        foreach ($this->getKeys() as $armyId) {
            $this->getArmy($armyId)->resetMovesLeft($gameId, $db);
        }
    }

    public function getArmyIdFromPosition($x, $y)
    {
        foreach ($this->_armies as $armyId => $army) {
            if ($x == $army->getX() && $y == $army->getY()) {
                return $armyId;
            }
        }
    }

    public function unfortify($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        foreach ($this->_armies as $army) {
            $army->setFortified(false, $gameId, $db);
        }
    }

    public function initFields($fields, $color)
    {
        foreach ($this->_armies as $armyId => $army) {
            $fields->addArmy($army->getX(), $army->getY(), $armyId, $color);
        }
    }

    public function noArmiesExists()
    {
        return !count($this->_armies);
    }

    public function armiesExists()
    {
        return count($this->_armies);
    }

    public function create($x, $y, $playerId, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mArmy = new Application_Model_Army($game->getId(), $db);
        $armyId = $mArmy->createArmy(array('x' => $x, 'y' => $y), $playerId);
        $army = new Cli_Model_Army(array(
            'armyId' => $armyId,
            'x' => $x,
            'y' => $y
        ), $game->getPlayerColor($playerId));
        $this->addArmy($armyId, $army);

        return $armyId;
    }

    public function moveHero($oldArmyId, $newArmyId, $heroId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->getArmy($newArmyId)->addHero($heroId, $this->getArmy($oldArmyId)->getHeroes()->getHero($heroId), $gameId, $db);
        $this->getArmy($oldArmyId)->getHeroes()->remove($heroId);
    }

    public function moveSoldier($oldArmyId, $newArmyId, $soldierId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->getArmy($newArmyId)->addSoldier($soldierId, $this->getArmy($oldArmyId)->getSoldiers()->getSoldier($soldierId), $gameId, $db);
        $this->getArmy($oldArmyId)->getSoldiers()->remove($soldierId);
    }

    public function count()
    {
        return count($this->_armies);
    }
}