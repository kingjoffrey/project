<?php

class Cli_Model_Being
{
    protected $_id;
    protected $_movesLeft;
    protected $_remainingLife;
    protected $_tmpLife;

    protected $_type;

    protected $_attack;
    protected $_defense;
    protected $_moves;
    protected $_lifePoints;
    protected $_regenerationSpeed;

    public function setMovesLeft($movesLeft)
    {
        $this->_movesLeft = $movesLeft;
    }

    public function getMovesLeft()
    {
        return $this->_movesLeft;
    }

    public function setRemainingLife($remainingLife)
    {
        $this->_remainingLife = $remainingLife;
    }

    public function setTmpLife($tmpLife)
    {
        $this->_tmpLife = $tmpLife;
    }

    public function resetTmpLife()
    {
        $this->_tmpLife = $this->_remainingLife;
    }

    public function getRemainingLife()
    {
        return $this->_remainingLife;
    }

    public function getTmpLife()
    {
        return $this->_tmpLife;
    }

    public function getLifePoints()
    {
        return $this->_lifePoints;
    }

    public function setRegenerationSpeed($regenerationSpeed)
    {
        $this->_regenerationSpeed = $regenerationSpeed;
    }

    public function getRegenerationSpeed()
    {
        return $this->_regenerationSpeed;
    }

    public function getAttackPoints()
    {
        return $this->_attack;
    }

    public function getDefensePoints()
    {
        return $this->_defense;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getMoves()
    {
        return $this->_moves;
    }
}