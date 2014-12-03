<?php

abstract class Cli_Model_DefaultPlayer
{
    protected $_castles;
    protected $_towers;

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

    public function getTowers()
    {
        return $this->_towers;
    }

    public function removeTower($towerId)
    {
        $this->_towers->removeTower($towerId);
    }

    public function removeCastle($castleId)
    {
        $this->_castles->removeCastle($castleId);
    }
}
