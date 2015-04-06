<?php

abstract class Cli_Model_DefaultPlayer
{
    protected $_id;

    protected $_color;
    protected $_team;

    protected $_longName;
    protected $_backgroundColor;

    protected $_armies;
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

    public function getArmies()
    {
        return $this->_armies;
    }

    public function getTeam()
    {
        return $this->_team;
    }

    public function getId()
    {
        return $this->_id;
    }
}
