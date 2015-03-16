<?php

class Cli_Model_BattleResultDefenders
{
    private $_defenders = array();

    public function toArray()
    {
        $defenders = array();
        foreach (array_keys($this->_defenders) as $color) {
            $defenders[$color] = array();
            foreach (array_keys($this->_defenders[$color]) as $armyId) {
                $defenders[$color][$armyId] = $this->_defenders[$color][$armyId]->toArray();
            }
        }
        return $defenders;
    }

    /**
     * @param $color
     * @param $armyId
     * @param $type
     * @return Cli_Model_BattleResultContainer
     */
    public function get($color, $armyId, $type)
    {
        if (!isset($this->_defenders[$color][$armyId])) {
            $this->_defenders[$color][$armyId] = new Cli_Model_BattleResultArmyContainer();
        }
        return $this->_defenders[$color][$armyId]->get($type);
    }
}