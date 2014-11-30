<?php

class Cli_Model_Being
{
    protected $_id;
    protected $_movesLeft;

    protected $_type;

    protected $_attack;
    protected $_defense;
    protected $_moves;

    public function setMovesLeft($movesLeft)
    {
        $this->_movesLeft = $movesLeft;
    }

    public function getMovesLeft()
    {
        return $this->_movesLeft;
    }

    public function getAttackPoints()
    {
        return $this->_attack;
    }

    public function getDefensePoints()
    {
        return $this->_defense;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getId()
    {
        return $this->_id;
    }
}