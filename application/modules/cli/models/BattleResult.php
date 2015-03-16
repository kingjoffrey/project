<?php

class Cli_Model_BattleResult
{
    private $_attack = array();
    private $_defenders = array();
    private $_castleId = null;
    private $_towerId = null;
    private $_victory = false;

    public function __construct()
    {
        $this->_attack = new Cli_Model_BattleResultArmyContainer();
    }

    public function toArray()
    {
        if ($this->_defenders || $this->_castleId) {
            return array(
                'attack' => $this->_attack->toArray(),
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
     * @return Cli_Model_BattleResultContainer
     */
    public function getDefending($color, $armyId, $type)
    {
        if (!isset($this->_defenders[$color][$armyId])) {
            $this->_defenders[$color][$armyId] = new Cli_Model_BattleResultArmyContainer();
        }
        return $this->_defenders[$color][$armyId]->get($type);
    }

    public function getVictory()
    {
        return $this->_victory;
    }
}