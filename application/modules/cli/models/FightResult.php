<?php

class Cli_Model_FightResult
{
    private $_attackerArmy = array();
    private $_defenderArmy = array();
    private $_defenderColor;
    private $_battle;
    private $_victory = false;

    public function setDefenderColor($color)
    {
        $this->_defenderColor = $color;
    }

    public function getDefenderColor()
    {
        return $this->_defenderColor;
    }
}