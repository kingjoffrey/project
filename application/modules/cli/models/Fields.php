<?php

class Cli_Model_Fields
{
    private $_fields;
    private $_default;

    public function __construct($fields)
    {
        $this->_default = $fields;
        foreach ($fields as $y => $row) {
            foreach ($row as $x => $type) {
                $this->_fields[$y][$x] = new Cli_Model_Field($type);
            }
        }
    }

    public function getType($x, $y)
    {
        return $this->_fields[$y][$x]->getType();
    }


    public function changeCastle($x, $y, $castleId, $type, $color)
    {
        $this->_fields[$y][$x]->setCastle($castleId, $type, $color);
        $this->_fields[$y + 1][$x]->setCastle($castleId, $type, $color);
        $this->_fields[$y][$x + 1]->setCastle($castleId, $type, $color);
        $this->_fields[$y + 1][$x + 1]->setCastle($castleId, $type, $color);
    }

    public function changeArmy($x, $y, $armyId, $type, $color)
    {
        $this->_fields[$y][$x]->setArmy($armyId, $type, $color);
    }

    public function changeTower($x, $y, $towerId, $color)
    {
        $this->_fields[$y][$x]->setTower($towerId, $color);
    }

    public function changeRuin($x, $y, $ruinId, $empty)
    {
        $this->_fields[$y][$x]->setRuin($ruinId, $empty);
    }

    public function getField($x, $y)
    {
        return $this->_fields[$y][$x];
    }

    public function isField($x, $y)
    {
        return isset($this->_fields[$y][$x]);
    }

    public function isMyCastle($x, $y)
    {
        if ($this->_fields[$y][$x]->getPossessionType() == 'myCastle') {
            return $this->_fields[$y][$x]->getCastleId();
        }
    }

    public function isPlayerCastle($color, $x, $y)
    {
        if ($this->_fields[$y][$x]->getColor() == $color) {
            return $this->_fields[$y][$x]->getCastleId();
        }
    }

    public function toArray()
    {
        $fields = array();
        foreach ($this->_fields as $y => $row) {
            $fields[$y] = array();
            foreach ($row as $x => $type) {
                $fields[$y][$x] = $this->_fields[$y][$x]->getType();
            }
        }
        return $fields;
    }

    public function isTower($x, $y)
    {
        return $this->_fields[$y][$x]->getTowerId();
    }

    public function isArmy($x, $y)
    {
        return $this->_fields[$y][$x]->getArmyId();
    }

    public function isTowerOpen($x, $y, $myColor, $myTeam)
    {
        $towerColor = $this->_fields[$y][$x]->getColor();
        if ($myColor != $towerColor && $myTeam != $towerColor) {
            return $towerColor;
        }
    }

    public function areUnitsAtCastlePosition($x, $y)
    {
        if ($this->isArmy($x, $y)) {
            return true;
        }
        if ($this->isArmy($x + 1, $y)) {
            return true;
        }
        if ($this->isArmy($x, $y + 1)) {
            return true;
        }
        if ($this->isArmy($x + 1, $y + 1)) {
            return true;
        }
    }
}