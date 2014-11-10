<?php

class Cli_Model_Fields
{
    private $_fields;

    public function __construct($fields)
    {
        foreach ($fields as $y => $x) {
            $this->_fields[$y][$x]=new Cli_Model_Field($x)
        }
    }

    public function get()
    {
        return $this->_fields;
    }

    public function changeCastle($x, $y, $castleId, $color)
    {
        $this->_fields[$y][$x]->setPossession
    }

    public function changeArmy($x, $y, $armyId, $color)
    {

    }
}