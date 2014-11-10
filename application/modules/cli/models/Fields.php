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
        $this->_fields[$y][$x]->setPossession($castleId, $type, $color);
        $this->_fields[$y + 1][$x]->setPossession($castleId, $type, $color);
        $this->_fields[$y][$x + 1]->setPossession($castleId, $type, $color);
        $this->_fields[$y + 1][$x + 1]->setPossession($castleId, $type, $color);
    }

    public function changeArmy($x, $y, $armyId, $type, $color)
    {
        $this->_fields[$y][$x]->setPossession($armyId, $type, $color);
    }

    public function changeTower($x, $y, $towerId, $color)
    {
        $this->_fields[$y][$x]->setPossession($towerId, 'tower', $color);
    }

    public function changeRuin($x, $y, $ruinId, $empty)
    {
        $this->_fields[$y][$x]->setPossession($ruinId, 'ruin', $empty);
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
            return $this->_fields[$y][$x]->getPossessionId();
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
}