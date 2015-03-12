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

    public function getAStarType($x, $y, $color, $destX, $destY, Cli_Model_Players $players)
    {
        if (isset($this->_fields[$y][$x])) {
            $field = $this->getField($x, $y);
            if ($castleId = $field->getCastleId()) {
                if ($players->sameTeam($field->getCastleColor(), $color)) {
                    return 'c';
                } else {
                    if ($destX == $x && $destY == $y) {
                        return 'E';
                    } elseif ($castleId == $this->getField($destX, $destY)->getCastleId()) {
                        return 'E';
                    } else {
                        return 'e';
                    }
                }
            } elseif ($armies = $field->getArmies()) {
                foreach ($armies as $armyId => $armyColor) {
                    if ($players->sameTeam($armyColor, $color)) {
                        if ($field->getType() == 'w' && $armyColor == $color) {
                            return 'S';
                        } else {
                            return $field->getType();
                        }
                    } else {
                        if ($destX == $x && $destY == $y) {
                            return 'E';
                        } else {
                            return 'e';
                        }
                    }
                }
            } else {
                return $field->getType();
            }
        }
    }

    public function isPlayerCastle($color, $x, $y)
    {
        if ($this->getField($x, $y)->getCastleColor() == $color) {
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
}