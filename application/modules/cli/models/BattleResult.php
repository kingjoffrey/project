<?php

class Cli_Model_BattleResult
{
    private $_attack;
    private $_defenders;
    private $_castleId = null;
    private $_towerId = null;
    private $_victory = false;

    public function __construct()
    {
        $this->_attack = new Cli_Model_BattleResultArmyContainer();
        $this->_defenders = new Cli_Model_BattleResultDefenders();
    }

    public function toArray()
    {
        if ($this->_defenders->hasDefenders() || $this->_castleId) {
            return array(
                'attack' => $this->_attack->toArray(),
                'defenders' => $this->_defenders->toArray(),
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

    /**
     * @param $type
     * @return Cli_Model_BattleResultContainer
     */
    public function getAttacking($type)
    {
        return $this->_attack->get($type);
    }

    /**
     * @param $color
     * @param $armyId
     * @param $type
     * @return Cli_Model_BattleResultDefenders
     */
    public function getDefending($color, $armyId, $type)
    {
        return $this->_defenders->get($color, $armyId, $type);
    }

    public function getVictory()
    {
        return $this->_victory;
    }
}