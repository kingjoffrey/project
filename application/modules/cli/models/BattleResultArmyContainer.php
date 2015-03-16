<?php

class Cli_Model_BattleResultArmyContainer
{
    private $_army = array();

    public function __construct()
    {
        $this->_army = array(
            'hero' => new Cli_Model_BattleResultContainer(),
            'walk' => new Cli_Model_BattleResultContainer(),
            'swim' => new Cli_Model_BattleResultContainer(),
            'fly' => new Cli_Model_BattleResultContainer()
        );
    }

    public function toArray()
    {
        $army = array();
        foreach (array_keys($this->_army) as $type) {
            $army[$type] = $this->get($type)->toArray();
        }
        return $army;
    }

    /**
     * @param $type
     * @return Cli_Model_BattleResultContainer
     */
    public function get($type)
    {
        return $this->_army[$type];
    }
}