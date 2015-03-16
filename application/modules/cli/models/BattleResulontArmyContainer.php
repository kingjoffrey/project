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
        return $this->_army;
    }

    public function get($type)
    {
        return $this->_army[$type];
    }
}