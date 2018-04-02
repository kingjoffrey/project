<?php

class Cli_Model_Unit
{
    private $_attackPoints;
    private $_defensePoints;
    private $_lifePoints;
    private $_regenerationSpeed;
    private $_canFly;
    private $_canSwim;
    private $_cost;
    private $_modMovesForest;
    private $_modMovesHills;
    private $_modMovesSwamp;
    private $_numberOfMoves;
    private $_id;
    private $_special;
    private $_name;
    private $_nameLang;

    public function __construct($unit)
    {
        $this->_id = $unit['unitId'];
        $this->_attackPoints = $unit['attackPoints'];
        $this->_defensePoints = $unit['defensePoints'];
        $this->_lifePoints = $unit['lifePoints'];
        $this->_regenerationSpeed = $unit['regenerationSpeed'];
        $this->_canFly = $unit['canFly'];
        $this->_canSwim = $unit['canSwim'];
        $this->_cost = $unit['cost'];
        $this->_modMovesForest = $unit['modMovesForest'];
        $this->_modMovesHills = $unit['modMovesHills'];
        $this->_modMovesSwamp = $unit['modMovesSwamp'];
        $this->_numberOfMoves = $unit['numberOfMoves'];
        $this->_special = $unit['special'];
        $this->_name = $unit['name'];
        $this->_nameLang = $unit['name_lang'];
    }

    public function toArray()
    {
        return array(
            'id' => $this->_id,
            'a' => $this->_attackPoints,
            'd' => $this->_defensePoints,
            'l' => $this->_lifePoints,
            'rs' => $this->_regenerationSpeed,
            'canFly' => $this->_canFly,
            'canSwim' => $this->_canSwim,
            'cost' => $this->_cost,
            'f' => $this->_modMovesForest,
            's' => $this->_modMovesSwamp,
            'h' => $this->_modMovesHills,
            'moves' => $this->_numberOfMoves,
            'special' => $this->_special,
            'name' => $this->_name,
            'name_lang' => $this->_nameLang
        );
    }

    public function getSpecial()
    {
        return $this->_special;
    }

    public function canSwim()
    {
        return $this->_canSwim;
    }

    public function getModMovesForest()
    {
        return $this->_modMovesForest;
    }

    public function getModMovesHills()
    {
        return $this->_modMovesHills;
    }

    public function getModMovesSwamp()
    {
        return $this->_modMovesSwamp;
    }

    public function canFly()
    {
        return $this->_canFly;
    }

    public function getCost()
    {
        return $this->_cost;
    }

    public function getAttackPoints()
    {
        return $this->_attackPoints;
    }

    public function getDefensePoints()
    {
        return $this->_defensePoints;
    }

    public function getNumberOfMoves()
    {
        return $this->_numberOfMoves;
    }

    public function getLifePoints()
    {
        return $this->_lifePoints;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getName()
    {
        return $this->_name;
    }
}
