<?php

class Cli_Model_Field
{
    private $_type;
    private $_temporaryType;
    private $_armyId;
    private $_castleId;
    private $_ruinId;
    private $_towerId;
    private $_color;

    public function __construct($type)
    {
        $this->_type = $type;
    }

    public function setArmy($armyId, $color)
    {
        $this->_armyId = $armyId;
        $this->_color = $color;
    }

    public function getArmyId()
    {
        return $this->_armyId;
    }

    public function setCastle($castleId, $color)
    {
        $this->_castleId = $castleId;
        $this->_color = $color;
    }

    public function getCastleId()
    {
        return $this->_castleId;
    }

    public function setRuin($ruinId, $empty)
    {
        $this->_ruinId = $ruinId;
        $this->_color = $empty;
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
        if ($this->_temporaryType) {
            return $this->_temporaryType;
        } else {
            return $this->_type;
        }
    }

    public function getColor()
    {
        return $this->_color;
    }

    public function setColor($color)
    {
        $this->_color = $color;
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