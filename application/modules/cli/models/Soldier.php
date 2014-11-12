<?php

/**
 * Class Cli_Model_Soldier
 * ver. 0001
 */
class Cli_Model_Soldier
{
    private $_unitId;
    private $_movesLeft;

    private $_forest;
    private $_hills;
    private $_swamp;

    private $_fly;
    private $_swim;

    private $_attack;
    private $_defense;
    private $_moves;

    public function __construct($soldier)
    {
        $this->_unitId = $soldier['unitId'];
        $this->_movesLeft = $soldier['movesLeft'];

        $units = Zend_Registry::get('units');

        $this->_forest = $units[$this->_unitId]['modMovesForest'];
        $this->_hills = $units[$this->_unitId]['modMovesHills'];
        $this->_swamp = $units[$this->_unitId]['modMovesSwamp'];

        $this->_fly = $units[$this->_unitId]['canFly'];
        $this->_swim = $units[$this->_unitId]['canSwim'];

        $this->_attack = $units[$this->_unitId]['attackPoints'];
        $this->_defense = $units[$this->_unitId]['defensePoints'];
        $this->_moves = $units[$this->_unitId]['numberOfMoves'];
    }

    public function toArray()
    {
        return array(
            'unitId' => $this->_unitId,
            'movesLeft' => $this->_movesLeft,
            'forest' => $this->_forest,
            'hills' => $this->_hills,
            'swamp' => $this->_swamp,
            'fly' => $this->_fly,
            'swim' => $this->_swim,
            'attack' => $this->_attack,
            'defense' => $this->_defense,
            'moves' => $this->_moves
        );
    }

    public function setMovesLeft($movesLeft)
    {
        $this->_movesLeft = $movesLeft;
    }

    public function getMovesLeft()
    {
        return $this->_movesLeft;
    }

    public function canFly()
    {
        return $this->_fly;
    }

    public function canSwim()
    {
        return $this->_swim;
    }

    public function updateMovesLeft($soldierId, $movesSpend, Application_Model_UnitsInGame $mSoldier)
    {
        $this->_movesLeft -= $movesSpend;
        if ($this->_movesLeft < 0) {
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('movesLeft < 0');
        }

        $mSoldier->updateMovesLeft($this->_movesLeft, $soldierId);
    }
}