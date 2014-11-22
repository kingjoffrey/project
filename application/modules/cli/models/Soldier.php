<?php

/**
 * Class Cli_Model_Soldier
 * ver. 0001
 */
class Cli_Model_Soldier extends Cli_Model_DefaultUnit
{
    protected $_type = 'soldier';

    private $_forest;
    private $_hills;
    private $_swamp;

    private $_fly;
    private $_swim;

    private $_used;

    public function __construct($soldier, $unit)
    {
        $this->_id = $soldier['soldierId'];
        $this->_unitId = $soldier['unitId'];

        if (isset($soldier['movesLeft'])) {
            $this->setMovesLeft($soldier['movesLeft']);
        } else {
            $this->setMovesLeft($unit['numberOfMoves']);
        }

        $this->_forest = $unit['modMovesForest'];
        $this->_hills = $unit['modMovesHills'];
        $this->_swamp = $unit['modMovesSwamp'];

        $this->_fly = $unit['canFly'];
        $this->_swim = $unit['canSwim'];

        $this->_attack = $unit['attackPoints'];
        $this->_defense = $unit['defensePoints'];
        $this->_moves = $unit['numberOfMoves'];
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

    public function resetMovesLeft($gameId, $db)
    {
        if ($this->_movesLeft > 2) {
            $this->setMovesLeft($this->_moves + 2);
        } else {
            $this->setMovesLeft($this->_moves);
        }

        $mUnitsInGame = new Application_Model_UnitsInGame($gameId, $db);
        $mUnitsInGame->updateMovesLeft($this->_movesLeft, $this->_id);
    }

    public function getForest()
    {
        return $this->_forest;
    }

    public function getHills()
    {
        return $this->_hills;
    }

    public function getSwamp()
    {
        return $this->_swamp;
    }

    /**
     * @param boolean $used
     */
    public function setUsed($used)
    {
        $this->_used = $used;
    }

    /**
     * @return boolean
     */
    public function notUsed()
    {
        return !$this->_used;
    }

    public function getUnitId()
    {
        return $this->_unitId;
    }
}