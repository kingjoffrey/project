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
        if ($this->_defenders || $this->_castleId) {
            return array(
                'attack' => $this->_attack,
                'defenders' => $this->_defenders,
                'castleId' => $this->_castleId,
                'towerId' => $this->_towerId,
                'victory' => $this->_victory
            );
        }
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

    public function isAttackingHeroDead($heroId)
    {
        if (isset($this->_attack['heroes'][$heroId])) {
            return true;
        }
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

    public function isAttackingSoldierDead($soldierId)
    {
        if (isset($this->_attack['soldiers'][$soldierId])) {
            return true;
        }
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

    public function addDefendingHeroSuccession($color, $armyId, $heroId, $succession)
    {
        $this->_defenders[$color][$armyId]['heroes'][$heroId] = $succession;
    }

    public function addDefendingHero($color, $armyId, $heroId)
    {
        if (isset($this->_defenders[$color][$armyId]['heroes'][$heroId])) {
            return true;
        }
        $this->_defenders[$color][$armyId]['heroes'][$heroId] = null;
    }

    public function isDefendingHeroDead($color, $armyId, $heroId)
    {
        if (isset($this->_defenders[$color][$armyId]['heroes'][$heroId])) {
            return true;
        }
    }

    public function addDefendingSoldierSuccession($color, $armyId, $soldierId, $succession)
    {
        $this->_defenders[$color][$armyId]['soldiers'][$soldierId] = $succession;
    }

    public function addDefendingSoldier($color, $armyId, $soldierId)
    {
        if (isset($this->_defenders[$color][$armyId]['soldiers'][$soldierId])) {
            return true;
        }
        $this->_defenders[$color][$armyId]['soldiers'][$soldierId] = null;
    }

    public function isDefendingSoldierDead($color, $armyId, $soldierId)
    {
        if (isset($this->_defenders[$color][$armyId]['soldiers'][$soldierId])) {
            return true;
        }
    }

    public function addDefendingShip($color, $armyId, $soldierId)
    {
        if (isset($this->_defenders[$color][$armyId]['soldiers'][$soldierId])) {
            $this->_defenders[$color][$armyId]['ships'][$soldierId] = $this->_defenders[$color][$armyId]['soldiers'][$soldierId];
            unset($this->_defenders[$color][$armyId]['soldiers'][$soldierId]);
            return true;
        }
        $this->_defenders[$color][$armyId]['ships'][$soldierId] = null;
    }

    public function getVictory()
    {
        return $this->_victory;
    }
}