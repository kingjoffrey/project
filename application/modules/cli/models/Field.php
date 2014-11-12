<?php

class Cli_Model_Field
{
    private $_type;
    private $_armyId;
    private $_castleId;
    private $_ruinId;
    private $_towerId;
    private $_possessionType;
    private $_color;

    public function __construct($type)
    {
        $this->_type = $type;
    }

    public function setArmy($armyId, $type, $color)
    {
        $this->_armyId = $armyId;
        $this->_possessionType = $type;
        $this->_color = $color;
    }

    public function getArmyId()
    {
        return $this->_armyId;
    }

    public function setCastle($castleId, $type, $color)
    {
        $this->_castleId = $castleId;
        $this->_possessionType = $type;
        $this->_color = $color;
    }

    public function getCastleId()
    {
        return $this->_castleId;
    }

    public function setRuin($ruinId, $type)
    {
        $this->_ruinId = $ruinId;
        $this->_possessionType = $type;
    }

    public function setTower($towerId, $color)
    {
        $this->_towerId = $towerId;
        $this->_color = $color;
    }

    public function getTowerId()
    {
        return $this->_towerId;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getPossessionId()
    {
        return $this->_possessionId;
    }

    public function getPossessionType()
    {
        return $this->_possessionType;
    }

    public function getColor()
    {
        return $this->_color;
    }
}