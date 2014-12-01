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

    public function getTower($towerId)
    {
        return $this->_towers[$towerId];
    }
}
