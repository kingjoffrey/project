<?php

class Cli_Model_BattleResult
{
    private $_attack = array(
        'heroes' => array(),
        'soldiers' => array(),
        'ships' => array()
    );
    private $_defence = array(
        'heroes' => array(),
        'soldiers' => array(),
        'ships' => array()
    );

    public function __construct()
    {
    }

    public function addAttackingHeroSuccession($heroId, $succession)
    {
        $this->_attack['heroes'][$heroId] = $succession;
    }

    public function addAttackingHero($heroId)
    {
        if (isset($this->_attack['heroes'][$heroId])) {
            return;
        }
        $this->_attack['heroes'][$heroId] = null;
    }

    public function addAttackingSoldierSuccession($soldierId, $succession)
    {
        $this->_attack['soldiers'][$soldierId] = $succession;
    }

    public function addAttackingSoldier($soldierId)
    {
        if (isset($this->_attack['soldiers'][$soldierId])) {
            return;
        }
        $this->_attack['soldiers'][$soldierId] = null;
    }

    public function addAttackingShip($soldierId)
    {
        if (isset($this->_attack['soldiers'][$soldierId])) {
            $this->_attack['ships'][$soldierId] = $this->_attack['soldiers'][$soldierId];
            unset($this->_attack['soldiers'][$soldierId]);
            return;
        }
        $this->_attack['ships'][$soldierId] = null;
    }

    public function addDefendingHeroSuccession($heroId, $succession)
    {
        $this->_defence['heroes'][$heroId] = $succession;
    }

    public function addDefendingHero($heroId)
    {
        if (isset($this->_defence['heroes'][$heroId])) {
            return;
        }
        $this->_defence['heroes'][$heroId] = null;
    }

    public function addDefendingSoldierSuccession($soldierId, $succession)
    {
        $this->_defence['soldiers'][$soldierId] = $succession;
    }

    public function addDefendingSoldier($soldierId)
    {
        if (isset($this->_defence['soldiers'][$soldierId])) {
            return;
        }
        $this->_defence['soldiers'][$soldierId] = null;
    }

    public function addDefendingShip($soldierId)
    {
        if (isset($this->_defence['soldiers'][$soldierId])) {
            $this->_defence['ships'][$soldierId] = $this->_defence['soldiers'][$soldierId];
            unset($this->_defence['soldiers'][$soldierId]);
            return;
        }
        $this->_defence['ships'][$soldierId] = null;
    }
}