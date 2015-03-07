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

    public function setCastleTemporaryType($x, $y, $type)
    {
        for ($i = $y; $i <= $y + 1; $i++) {
            for ($j = $x; $j <= $x + 1; $j++) {
                $this->_fields[$i][$j]->setTemporaryType($type);
            }
        }
    }

    public function resetCastleTemporaryType($x, $y)
    {
        for ($i = $y; $i <= $y + 1; $i++) {
            for ($j = $x; $j <= $x + 1; $j++) {
                $this->_fields[$i][$j]->reset();
            }
        }
    }

    public function isPlayerArmy($x, $y, $playerColor)
    {
        foreach ($this->getField($x, $y)->getArmies() as $armyId => $color) {
            if ($color == $playerColor) {
                return $armyId;
            }
        }
    }

    /**
     * @param $x
     * @param $y
     * @return Cli_Model_Field
     * @throws Exception
     */
    public function getField($x, $y)
    {
        if (!isset($this->_fields[$y][$x])) {
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('no such field');
        }
        return $this->_fields[$y][$x];
    }

    public function hasField($x, $y)
    {
        return isset($this->_fields[$y][$x]);
    }

    public function getAStarType($x, $y)
    {
        if (isset($this->_fields[$y][$x])) {
            return $this->_fields[$y][$x]->getType();
        }
    }

    public function getCastleColor($x, $y)
    {
        return $this->getField($x, $y)->getCastleColor();
    }

    public function isPlayerCastle($color, $x, $y)
    {
        if ($this->getField($x, $y)->getCastleColor() == $color) {
            return $this->getField($x, $y)->getCastleId();
        }
    }

    public function isEnemyCastle($color, $x, $y)
    {
        if ($this->getField($x, $y)->getCastleColor() != $color) {
            return $this->getField($x, $y)->getCastleId();
        }
    }

    public function toArray()
    {
        $fields = array();
        foreach ($this->_fields as $y => $row) {
            $fields[$y] = array();
            foreach ($row as $x => $type) {
                $fields[$y][$x] = $this->getField($x, $y)->toArray();
            }
        }
        return $fields;
    }

    public function isTower($x, $y)
    {
        return $this->getField($x, $y)->getTowerId();
    }

    public function isRuin($x, $y)
    {
        return $this->getField($x, $y)->getRuinId();
    }

    public function areArmiesInCastle($x, $y)
    {
        for ($i = $y; $i <= $y + 1; $i++) {
            for ($j = $x; $j <= $x + 1; $j++) {
                if ($this->_fields[$i][$j]->isArmy()) {
                    return true;
                }
            }
        }
    }

    public function getCastleId($x, $y)
    {
        return $this->getField($x, $y)->getCastleId();
    }

    public function getTowerId($x, $y)
    {
        return $this->getField($x, $y)->getTowerId();
    }

    public function getTowerColor($x, $y)
    {
        return $this->getField($x, $y)->getTowerColor();
    }
}