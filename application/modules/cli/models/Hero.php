<?php

/**
 * Class Cli_Model_Hero
 * ver. 0001
 */
class Cli_Model_Hero
{
    private $_moves;
    private $_attack;
    private $_defense;
    private $_name;
    private $_movesLeft;

    public function __construct($hero)
    {
        $this->_moves = $hero['numberOfMoves'];
        $this->_attack = $hero['attackPoints'];
        $this->_defense = $hero['defensePoints'];
        $this->_name = $hero['name'];
        $this->_movesLeft = $hero['movesLeft'];
    }

    public function toArray()
    {
        return array(
            'moves' => $this->_moves,
            'attack' => $this->_attack,
            'defense' => $this->_defense,
            'name' => $this->_name,
            'movesLeft' => $this->_movesLeft
        );
    }

    public function setMovesLeft($movesLeft)
    {
        $this->_movesLeft = $movesLeft;
    }
}