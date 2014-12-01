<?php

class Cli_Model_Armies
{
    private $_armies = array();

    public function get()
    {
        return $this->_armies;
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
        $x = $this->_armies[$excludedArmyId]->getX();
        $y = $this->_armies[$excludedArmyId]->getY();

        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mArmy = new Application_Model_Army($gameId, $db);

        $ids = array();

        foreach ($this->_armies as $armyId => $army) {
            if ($armyId == $excludedArmyId) {
                continue;
            }
            if ($x == $army->getX() && $y == $army->getY()) {
                $this->_armies[$excludedArmyId]->addHeroes($army->getHeroes());
                $this->_armies[$excludedArmyId]->addSoldiers($army->getSoldiers());
                unset($this->_armies[$armyId]);

                $mHeroesInGame->heroesUpdateArmyId($armyId, $excludedArmyId);
                $mSoldier->soldiersUpdateArmyId($armyId, $excludedArmyId);
                $mArmy->destroyArmy($armyId);

                $ids[] = $armyId;
            }
        }

        return $ids;
    }

    public function resetMovesLeft($gameId, $db)
    {
        foreach ($this->_armies as $army) {
            $army->resetMovesLeft($gameId, $db);
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

    public function unfortify($gameId, $db)
    {
        foreach ($this->_armies as $army) {
            $army->setFortified(false, $gameId, $db);
        }
    }

}