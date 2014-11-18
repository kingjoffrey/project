<?php

class Cli_Model_Field
{
    private $_type;
    private $_temporaryType;
    private $_armyId;
    private $_castleId;
    private $_towerId;
    private $_ruinId;
    private $_armyColor;
    private $_castleColor;
    private $_towerColor;
    private $_empty;

    public function __construct($type)
    {
        $this->_type = $type;
    }

    public function setArmy($armyId, $color)
    {
        $this->_armyId = $armyId;
        $this->_armyColor = $color;
    }

    public function setCastle($castleId, $color)
    {
        $this->_castleId = $castleId;
        $this->_castleColor = $color;
    }

    public function setTower($towerId, $color)
    {
        $this->_towerId = $towerId;
        $this->_towerColor = $color;
    }

    public function setRuin($ruinId, $empty)
    {
        $this->_ruinId = $ruinId;
        $this->_empty = $empty;
    }

    public function getEmpty()
    {
        return $this->_empty;
    }

    public function getArmyId()
    {
        return $this->_armyId;
    }

    public function getCastleId()
    {
        return $this->_castleId;
    }

    public function getTowerId()
    {
        return $this->_towerId;
    }

    public function getType()
    {
        if ($this->_temporaryType) {
            return $this->_temporaryType;
        } else {
            return $this->_type;
        }
    }

    public function getArmyColor()
    {
        return $this->_armyColor;
    }

    public function getCastleColor()
    {
        return $this->_castleColor;
    }

    public function getTowerColor()
    {
        return $this->_towerColor;
    }

    public function setArmyColor($color)
    {
        $this->_armyColor = $color;
    }

    public function setCastleColor($color)
    {
        $this->_castleColor = $color;
    }

    public function setTowerColor($color)
    {
        $this->_towerColor = $color;
    }

    public function setTemporaryType($type)
    {
        $this->_temporaryType = $type;
    }

    public function reset()
    {
        $this->_temporaryType = null;
    }
}