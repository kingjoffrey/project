<?php

/**
 * Class Cli_Model_Hero
 * ver. 0001
 */
class Cli_Model_Hero
{
    private $_numberOfMoves;
    private $_attackPoints;
    private $_defensePoints;
    private $_name;
    private $_movesLeft;

    public function __construct($hero)
    {
        $this->_numberOfMoves = $hero['numberOfMoves'];
        $this->_attackPoints = $hero['attackPoints'];
        $this->_defensePoints = $hero['defensePoints'];
        $this->_name = $hero['name'];
        $this->_movesLeft = $hero['movesLeft'];
    }

    public function toArray()
    {
        return array(
            'numberOfMoves' => $this->_numberOfMoves,
            'attackPoints' => $this->attackPoints_,
            'defensePoints' => $this->_defensePoints,
            'name' => $this->_name,
            'movesLeft' => $this->_movesLeft
        );
    }

    public function setMovesLeft($movesLeft)
    {
        $this->_movesLeft = $movesLeft;
    }
}