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


    public function initCastle($x, $y, $castleId, $color)
    {
        for ($i = $y; $i <= $y + 1; $i++) {
            for ($j = $x; $j <= $x + 1; $j++) {
                $this->_fields[$i][$j]->setCastle($castleId, $color);
            }
        }
    }

    public function razeCastle($x, $y)
    {
        for ($i = $y; $i <= $y + 1; $i++) {
            for ($j = $x; $j <= $x + 1; $j++) {
                $this->_fields[$i][$j]->setCastle(null, null);
            }
        }
    }

    public function changeCastle($x, $y, $color)
    {
        for ($i = $y; $i <= $y + 1; $i++) {
            for ($j = $x; $j <= $x + 1; $j++) {
                $this->_fields[$i][$j]->setCastleColor($color);
            }
        }
    }

    public function setCastleTemporaryType($x, $y, $type)
    {
        for ($i = $y; $i <= $y + 1; $i++) {
            for ($j = $x; $j <= $x + 1; $j++) {
                $this->_fields[$i][$j]->setTemporaryType($type);
            }
        }
    }

    public function setTemporaryType($x, $y, $type)
    {
        $this->_fields[$y][$x]->setTemporaryType($type);
    }

    public function resetTemporaryType($x, $y)
    {
        $this->_fields[$y][$x]->reset();
    }

    public function resetCastleTemporaryType($x, $y)
    {
        for ($i = $y; $i <= $y + 1; $i++) {
            for ($j = $x; $j <= $x + 1; $j++) {
                $this->_fields[$i][$j]->reset();
            }
        }
    }

    public function addArmy($x, $y, $armyId, $color)
    {
        $this->_fields[$y][$x]->addArmy($armyId, $color);
    }

    public function initTower($x, $y, $towerId, $color)
    {
        $this->_fields[$y][$x]->setTower($towerId, $color);
    }

    public function changeTower($x, $y, $color)
    {
        $this->_fields[$y][$x]->setTowerColor($color);
    }

    public function initRuin($x, $y, $ruinId, $empty)
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

    public function isPlayerCastle($color, $x, $y)
    {
        if ($this->_fields[$y][$x]->getColor() == $color) {
            return $this->_fields[$y][$x]->getCastleId();
        }
    }

    public function isEnemyCastle($color, $x, $y)
    {
        if ($this->_fields[$y][$x]->getColor() != $color) {
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

    public function isTowerOpen($x, $y, $myColor, $myTeam)
    {
        $towerColor = $this->_fields[$y][$x]->getTowerColor();
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

    public function getCastleId($x, $y)
    {
        return $this->_fields[$y][$x]->getCastleId();
    }
}