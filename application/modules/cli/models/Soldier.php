<?php

/**
 * Class Cli_Model_Soldier
 * ver. 0001
 */
class Cli_Model_Soldier extends Cli_Model_Being
{
    protected $_type = 'soldier';

    private $_forest;
    private $_hills;
    private $_swamp;

    private $_fly;
    private $_swim;

    private $_cost;

    public function __construct($soldier, $unit)
    {
        $this->_id = $soldier['soldierId'];
        $this->_unitId = $soldier['unitId'];

        if (isset($soldier['movesLeft'])) {
            $this->setMovesLeft($soldier['movesLeft']);
        } else {
            $this->setMovesLeft($unit->getNumberOfMoves());
        }

        $this->_forest = $unit->getModMovesForest();
        $this->_hills = $unit->getModMovesHills();
        $this->_swamp = $unit->getModMovesSwamp();

        $this->_fly = $unit->canFly();
        $this->_swim = $unit->canSwim();

        $this->_cost = $unit->getCost();

        $this->_attack = $unit->getAttackPoints();
        $this->_defense = $unit->getDefensePoints();
        $this->_moves = $unit->getNumberOfMoves();
    }

    public function toArray()
    {
        return array(
            'unitId' => $this->_unitId,
            'movesLeft' => $this->_movesLeft
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
            echo 'movesLeft= ' . $this->_movesLeft . "\n";
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('movesLeft < 0');
        }

        $mSoldier->updateMovesLeft($this->_movesLeft, $soldierId);
    }

    public function resetMovesLeft($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        if ($this->_movesLeft > 2) {
            $this->setMovesLeft($this->_moves + 2);
        } else {
            $this->setMovesLeft($this->_moves);
        }

        $mUnitsInGame = new Application_Model_UnitsInGame($gameId, $db);
        $mUnitsInGame->updateMovesLeft($this->_movesLeft, $this->_id);
    }

    public function getUnitId()
    {
        return $this->_unitId;
    }

    public function death($gameId, Zend_Db_Adapter_Pdo_Pgsql $db, $winnerId, $loserId)
    {
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mSoldier->destroy($this->_id);

        $mSoldiersKilled = new Application_Model_SoldiersKilled($gameId, $db);
        $mSoldiersKilled->add($this->_unitId, $winnerId, $loserId);
    }

    public function getStepCost($terrain, $terrainType, $movementType)
    {
        if ($movementType == 'walking') {
            switch ($terrainType) {
                case 'f':
                    return $this->getForest();
                case 's':
                    return $this->getSwamp();
                case 'h':
                    return $this->getHills();
                default:
                    return $terrain->getTerrainType($terrainType)->getCost($movementType);
            }
        }
        return $terrain->getTerrainType($terrainType)->getCost($movementType);
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

    public function getCost()
    {
        return $this->_cost;
    }
}