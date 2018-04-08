<?php

/**
 * Class Cli_Model_Soldier
 * ver. 0001
 */
class Cli_Model_Soldier extends Cli_Model_Being
{
    private $_forest;
    private $_hills;
    private $_swamp;

    public function __construct($soldier, Cli_Model_Unit $unit)
    {
        $this->_id = $soldier['soldierId'];
        $this->_unitId = $soldier['unitId'];

        if ($unit->canSwim()) {
            $this->_type = 'swim';
        } elseif ($unit->canFly()) {
            $this->_type = 'fly';
        } else {
            $this->_type = 'walk';
        }

        if (isset($soldier['movesLeft'])) {
            $this->setMovesLeft($soldier['movesLeft']);
        } else {
            $this->setMovesLeft($unit->getNumberOfMoves());
        }

        if (isset($soldier['remainingLife'])) {
            $this->setRemainingLife($soldier['remainingLife']);
        } else {
            $this->setRemainingLife($unit->getLifePoints());
        }

        $this->_tmpLife = $this->_remainingLife;

        $this->_forest = $unit->getModMovesForest();
        $this->_hills = $unit->getModMovesHills();
        $this->_swamp = $unit->getModMovesSwamp();

        $this->_attack = $unit->getAttackPoints();
        $this->_defense = $unit->getDefensePoints();
        $this->_moves = $unit->getNumberOfMoves();
        $this->_lifePoints = $unit->getLifePoints();
        $this->_regenerationSpeed = $unit->getRegenerationSpeed();
    }

    public function toArray()
    {
        return array(
            'unitId' => $this->_unitId,
            'movesLeft' => $this->_movesLeft,
            'remainingLife' => $this->_remainingLife
        );
    }

    public function updateRemainingLife($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        if ($this->_tmpLife == $this->_remainingLife) {
            return;
        }

        $this->setRemainingLife($this->_tmpLife);

        $mUnitsInGame = new Application_Model_UnitsInGame($gameId, $db);
        $mUnitsInGame->updateRemainingLife($this->_tmpLife, $this->_id);
    }

    public function regenerateLife(Application_Model_UnitsInGame $mUnitsInGame)
    {
        if ($this->_remainingLife < $this->_lifePoints) {
            $this->_remainingLife += $this->_regenerationSpeed;
            if ($this->_remainingLife > $this->_lifePoints) {
                $this->setRemainingLife($this->_lifePoints);
            }
            $mUnitsInGame->updateRemainingLife($this->_remainingLife, $this->_id);
        }
    }

    public function updateMovesLeft($movesSpend, Application_Model_UnitsInGame $mUnitsInGame)
    {
        $this->_movesLeft -= $movesSpend;
        if ($this->_movesLeft < 0) {
            echo 'movesLeft= ' . $this->_movesLeft . "\n";
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            throw new Exception('movesLeft < 0');
        }

        $mUnitsInGame->updateMovesLeft($this->_movesLeft, $this->_id);
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

    public function getStepCost(Cli_Model_TerrainTypes $terrain, $terrainType, $movementType)
    {
        if ($movementType == 'walk') {
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
        } elseif ($movementType == 'swim' && $this->_type != 'swim') {
            return 0;
        } else {
            return $terrain->getTerrainType($terrainType)->getCost($movementType);
        }
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

    public function zeroMovesLeft($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_movesLeft = 0;
        $mUnitsInGame = new Application_Model_UnitsInGame($gameId, $db);
        $mUnitsInGame->updateMovesLeft($this->_movesLeft, $this->_id);
    }
}