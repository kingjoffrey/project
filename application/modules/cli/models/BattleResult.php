<?php

class Cli_Model_BattleResult
{
    private $_attack = array(
        'heroes' => array(),
        'soldiers' => array(),
        'ships' => array(),
    );
    private $_defenders = array();
    private $_castleId = null;
    private $_towerId = null;
    private $_victory = false;

    public function toArray()
    {
        return array(
            'attack' => $this->_attack,
            'defenders' => $this->_defenders,
            'castleId' => $this->_castleId,
            'towerId' => $this->_towerId,
            'victory' => $this->_victory
        );
    }

    public function victory()
    {
        $this->_victory = true;
    }

    public function setCastleId($castleId)
    {
        $this->_castleId = $castleId;
    }

    public function setTowerId($towerId)
    {
        $this->_towerId = $towerId;
    }

    public function addAttackingHeroSuccession($heroId, $succession)
    {
        $this->_attack['heroes'][$heroId] = $succession;
    }

    public function addAttackingHero($heroId)
    {
        if (isset($this->_attack['heroes'][$heroId])) {
            return true;
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
            return true;
        }
        $this->_attack['soldiers'][$soldierId] = null;
    }

    public function addAttackingShip($soldierId)
    {
        if (isset($this->_attack['soldiers'][$soldierId])) {
            $this->_attack['ships'][$soldierId] = $this->_attack['soldiers'][$soldierId];
            unset($this->_attack['soldiers'][$soldierId]);
            return true;
        }
        $this->_attack['ships'][$soldierId] = null;
    }

    public function addDefendingHeroSuccession($color, $heroId, $succession)
    {
        $this->_defenders[$color]['heroes'][$heroId] = $succession;
    }

    public function addDefendingHero($color, $heroId)
    {
        if (isset($this->_defenders[$color]['heroes'][$heroId])) {
            return true;
        }
        $this->_defenders[$color]['heroes'][$heroId] = null;
    }

    public function isDefendingHero($color, $heroId)
    {
        if (isset($this->_defenders[$color]['heroes'][$heroId])) {
            return true;
        }
    }

    public function addDefendingSoldierSuccession($color, $soldierId, $succession)
    {
        $this->_defenders[$color]['soldiers'][$soldierId] = $succession;
    }

    public function addDefendingSoldier($color, $soldierId)
    {
        if (isset($this->_defenders[$color]['soldiers'][$soldierId])) {
            return true;
        }
        $this->_defenders[$color]['soldiers'][$soldierId] = null;
    }

    public function isDefendingSoldier($color, $soldierId)
    {
        if (isset($this->_defenders[$color]['soldiers'][$soldierId])) {
            return true;
        }
    }

    public function addDefendingShip($color, $soldierId)
    {
        if (isset($this->_defenders[$color]['soldiers'][$soldierId])) {
            $this->_defenders[$color]['ships'][$soldierId] = $this->_defenders[$color]['soldiers'][$soldierId];
            unset($this->_defenders[$color]['soldiers'][$soldierId]);
            return true;
        }
        $this->_defenders[$color]['ships'][$soldierId] = null;
    }
}