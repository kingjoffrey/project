<?php

abstract class Cli_Model_DefaultPlayer
{
    protected $_castles = array();
    protected $_towers = array();

    public function castlesToArray()
    {
        $castles = array();
        foreach ($this->_castles as $castleId => $castle) {
            $castles[$castleId] = $castle->toArray();
        }
        return $castles;
    }

    public function towersToArray()
    {
        $towers = array();
        foreach ($this->_towers as $towerId => $tower) {
            $towers[$towerId] = $tower->toArray();
        }
        return $towers;
    }

    public function hasCastle($castleId)
    {
        return isset($this->_castles[$castleId]);
    }

    public function hasTower($towerId)
    {
        return isset($this->_towers[$towerId]);
    }

    public function noCastlesExists()
    {
        return !count($this->_castles);
    }

    public function castlesExists()
    {
        return count($this->_castles);
    }

    public function getCastles()
    {
        return $this->_castles;
    }

    public function getCastle($castleId)
    {
        return $this->_castles[$castleId];
    }

    public function getTowers()
    {
        return $this->_towers;
    }
}
