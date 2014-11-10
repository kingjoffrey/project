<?php

/**
 * Class Cli_Model_Soldier
 * ver. 0001
 */
class Cli_Model_Soldier
{
    private $_unitId;
    private $_movesLeft;

    public function __construct($soldier)
    {
        $this->_unitId = $soldier['unitId'];
        $this->_movesLeft = $soldier['movesLeft'];

        $units = Zend_Registry::get('units');

        $this->_road;
        //'modMovesForest', 'modMovesHills', 'modMovesSwamp'
    }

    public function toArray()
    {
        return array(
            'unitId' => $this->_unitId,
            'movesLeft' => $this->_movesLeft,
            'road' => $this->_road,
            'grass' => $this->_grass,
        );
    }

    public function setMovesLeft($movesLeft)
    {
        $this->_movesLeft = $movesLeft;
    }
}