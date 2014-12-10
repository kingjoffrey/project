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
        return count($this->_soldiers);
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

    public function getKeys()
    {
        return array_keys($this->_soldiers);
    }

    public function saveMove($x, $y, $movesLeft, $type, Cli_Model_Path $path, $gameId, $db)
    {
        if (!count($this->_soldiers)) {
            return $movesLeft;
        }

        $terrain = Zend_Registry::get('terrain');
        $current = $path->getCurrent();
        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);

        foreach ($this->getKeys() as $soldierId) {
            $soldier = $this->getSoldier($soldierId);
            $movesSpend = 0;

            if ($type == 'walking') {
                $terrain['f'][$type] = $soldier->getForest();
                $terrain['m'][$type] = $soldier->getHills();
                $terrain['s'][$type] = $soldier->getSwamp();
            }
            foreach ($current as $step) {
                if ($step['x'] == $x && $step['y'] == $y) {
                    break;
                }
                if (!isset($step['cc'])) {
                    $movesSpend += $terrain[$step['tt']][$type];
                }
            }

            $soldier->updateMovesLeft($soldierId, $movesSpend, $mSoldier);

            if ($movesLeft > $soldier->getMovesLeft()) {
                $movesLeft = $soldier->getMovesLeft();
            }
        }
    }

    public function resetMovesLeft($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        foreach ($this->getKeys() as $soldierId) {
            $this->getSoldier($soldierId)->resetMovesLeft($gameId, $db);
        }
    }
}