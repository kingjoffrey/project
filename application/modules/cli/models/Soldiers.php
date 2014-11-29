<?php

class Cli_Model_Soldiers
{
    private $_soldiers = array();

    public function get()
    {
        return $this->_soldiers;
    }

    public function addSoldier($soldierId, $soldier)
    {
        $this->_soldiers[$soldierId] = $soldier;
    }

    /**
     * @param $soldierId
     * @return Cli_Model_Soldier
     */
    public function getSoldier($soldierId)
    {
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

    public function exists()
    {
        return count($this->_soldiers);
    }

    public function add($soldiers)
    {
        $this->_soldiers = array_merge($this->_soldiers, $soldiers);
    }

    public function getCosts()
    {
        $costs = 0;
        foreach ($this->_soldiers as $soldier) {
            $costs += $soldier->getCost();
        }
        foreach ($this->_ships as $soldier) {
            $costs += $soldier->getCost();
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
}