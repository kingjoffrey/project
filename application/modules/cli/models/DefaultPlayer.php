<?php

abstract class Cli_Model_DefaultPlayer
{
    protected $_armies;
    protected $_castles;
    protected $_towers;

    protected $_color;

    public function hasTower($towerId)
    {
        return isset($this->_towers[$towerId]);
    }

    public function noCastlesExists()
    {
        return !count($this->_castles);
    }

    public function castlesExists()
    {
        return count($this->_castles);
    }

    public function getCastles()
    {
        return $this->_castles;
    }

    public function getTowers()
    {
        return $this->_towers;
    }

    public function removeTower($towerId)
    {
        $this->_towers->removeTower($towerId);
    }

    public function removeCastle($castleId)
    {
        $this->_castles->removeCastle($castleId);
    }

    /**
     * @param $fields Cli_Model_Fields
     */
    public function initFieldsTemporaryType(Cli_Model_Fields $fields)
    {
        foreach ($this->_armies->getKeys() as $armyId) {
            $army = $this->_armies->getArmy($armyId);
            $field = $fields->getField($army->getX(), $army->getY());
            $field->setTemporaryType('e');
        }

        foreach ($this->_castles->getKeys() as $castleId) {
            $castle = $this->_castles->getCastle($castleId);
            $x = $castle->getX();
            $y = $castle->getY();
            for ($i = $x; $i <= $x + 1; $i++) {
                for ($j = $y; $j <= $y + 1; $j++) {
                    $fields->getField($i, $j)->setTemporaryType('e');
                }
            }
        }

//        foreach ($this->_towers->getKeys() as $towerId) {
//            $tower = $this->_towers->getTower($towerId);
//            $field = $fields->getField($tower->getX(), $tower->getY());
//            $field->setTemporaryType('e');
//        }
    }
}
