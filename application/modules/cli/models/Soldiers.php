<?php

class Cli_Model_Soldiers
{
    private $_soldiers = array();

    public function get()
    {
        return $this->_soldiers;
    }

    public function add($soldierId, $soldier)
    {
        $this->_soldiers[$soldierId] = $soldier;
    }

    /**
     * @param $soldierId
     * @return Cli_Model_Soldier
     */
    public function getSoldier($soldierId)
    {
        if (!isset($this->_soldiers[$soldierId])) {
            throw new Exception('');
        }
        return $this->_soldiers[$soldierId];
    }

    public function toArray()
    {
        $soldiers = array();
        foreach ($this->_soldiers as $soldierId => $soldier) {
            $soldiers[$soldierId] = $soldier->toArray();
        }
        return $soldiers;
    }

    public function hasSoldier($soldierId)
    {
        return isset($this->_soldiers[$soldierId]);
    }

    public function exists()
    {
        return !empty($this->_soldiers);
    }

    public function getCosts()
    {
        $units = Zend_Registry::get('units');
        $costs = 0;
        foreach ($this->_soldiers as $soldier) {
            $costs += $units->getUnit($soldier->getUnitId())->getCost();
        }
        return $costs;
    }

    public function setDefenceBattleSequence($defenceBattleSequence)
    {
        $array = array();
        foreach ($defenceBattleSequence as $unitId) {
            foreach ($this->_soldiers as $soldierId => $soldier) {
                if ($soldier->getUnitId() == $unitId) {
                    $array[$soldierId] = $soldier;
                }
            }
        }
        return $array;
    }

    public function setAttackBattleSequence($attackBattleSequence)
    {
        $array = array();
        foreach ($attackBattleSequence as $unitId) {
            foreach ($this->_soldiers as $soldierId => $soldier) {
                if ($soldier->getUnitId() == $unitId) {
                    $array[$soldierId] = $soldier;
                }
            }
        }
        return $array;
    }

    public function remove($soldierId)
    {
        unset($this->_soldiers[$soldierId]);
    }

    public function getKeys()
    {
        return array_keys($this->_soldiers);
    }

    public function saveMove($x, $y, $movesLeft, $type, Cli_Model_Path $path, Cli_Model_TerrainTypes $terrain, $gameId, $db)
    {
        if (empty($this->_soldiers)) {
            return $movesLeft;
        }

        $current = $path->getCurrent();
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);

        foreach ($this->getKeys() as $soldierId) {
            $soldier = $this->getSoldier($soldierId);
            $movesSpend = 0;

            foreach ($current as $step) {
                if ($step['x'] == $x && $step['y'] == $y) {
                    break;
                }
                if (!$step['c']) {
                    $movesSpend += $soldier->getStepCost($terrain, $step['t'], $type);
                    echo 'step[t]=' . $step['t'] . '    movesSpend=' . $movesSpend . "\n";
                }
            }

            $soldier->updateMovesLeft($soldierId, $movesSpend, $mSoldier);

            if ($movesLeft > $soldier->getMovesLeft()) {
                $movesLeft = $soldier->getMovesLeft();
            }
        }
        return $movesLeft;
    }

    public function resetMovesLeft($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        foreach ($this->getKeys() as $soldierId) {
            $this->getSoldier($soldierId)->resetMovesLeft($gameId, $db);
        }
    }

    public function count()
    {
        return count($this->_soldiers);
    }
}