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

    protected $_allHeroes;

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

    /**
     * @return Cli_Model_Castles
     */
    public function getCastles()
    {
        return $this->_castles;
    }

    /**
     * @return Cli_Model_Towers
     */
    public function getTowers()
    {
        return $this->_towers;
    }

    /**
     * @return Cli_Model_Armies
     */
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

    public function getAllHeroes()
    {
        return $this->_allHeroes;
    }
}
