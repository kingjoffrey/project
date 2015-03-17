<?php

class Cli_Model_Field
{
    private $_type;
    private $_armies = array();
    private $_castleId;
    private $_towerId;
    private $_ruinId;
    private $_castleColor;
    private $_towerColor;

    public function __construct($type)
    {
        $this->_type = $type;
    }

    public function toArray()
    {
        return array(
            'type' => $this->_type
        );
    }

    public function addArmy($armyId, $color)
    {
        $this->_armies[$armyId] = $color;
//        echo 'ADD:'."\n";
//        print_r($this->_armies);
    }

    public function removeArmy($armyId)
    {
        unset($this->_armies[$armyId]);
//        echo 'REMOVE:'."\n";
//        print_r($this->_armies);
    }

    public function isArmy()
    {
        return !empty($this->_armies);
    }

    public function getArmyColor($armyId)
    {
        if (empty($armyId)) {
            return 'neutral';
        }
        return $this->_armies[$armyId];
    }

    public function getArmies()
    {
        if (is_array($this->_armies)) {
            return $this->_armies;
        } else {
            return array();
        }
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

    public function setRuin($ruinId)
    {
        $this->_ruinId = $ruinId;
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
        return $this->_type;
    }

    public function getCastleColor()
    {
        return $this->_castleColor;
    }

    public function getTowerColor()
    {
        return $this->_towerColor;
    }

    public function setCastleColor($color)
    {
        $this->_castleColor = $color;
    }

    public function setTowerColor($color)
    {
        $this->_towerColor = $color;
    }

    public function getRuinId()
    {
        return $this->_ruinId;
    }
}