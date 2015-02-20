<?php

class Cli_Model_Towers
{
    private $_towers = array();

    public function toArray()
    {
        $towers = array();
        foreach ($this->_towers as $towerId => $tower) {
            $towers[$towerId] = $tower->toArray();
        }
        return $towers;
    }

    public function get()
    {
        return $this->_towers;
    }

    public function getKeys()
    {
        return array_keys($this->_towers);
    }

    public function add($towerId, Cli_Model_Tower $tower, $oldColor = null, $playerId = null, $gameId = null, $db = null)
    {
        $this->_towers[$towerId] = $tower;
        if ($db) {
            $mTowersInGame = new Application_Model_TowersInGame($gameId, $db);
            if ($oldColor == 'neutral') {
                $mTowersInGame->addTower($towerId, $playerId);
            } else {
                $mTowersInGame->changeTowerOwner($towerId, $playerId);
            }
        }
    }

    /**
     * @param $towerId
     * @return Cli_Model_Tower
     */
    public function getTower($towerId)
    {
        return $this->_towers[$towerId];
    }

    public function removeTower($towerId)
    {
        unset($this->_towers[$towerId]);
    }

    public function initFields(Cli_Model_Fields $fields, $color)
    {
        foreach ($this->_towers as $towerId => $tower) {
            $fields->getField($tower->getX(), $tower->getY())->setTower($towerId, $color);
        }
    }

    public function count()
    {
        return count($this->_towers);
    }
}
