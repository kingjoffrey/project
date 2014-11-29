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
}