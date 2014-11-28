<?php

class Cli_Model_Armies
{
    private $_armies = array();

    public function get()
    {
        return $this->_armies;
    }

    public function addArmy($armyId, $army)
    {
        $this->_armies[$armyId] = $army;
    }

    /**
     * @param $armyId
     * @return Cli_Model_Army
     */
    public function getArmy($armyId)
    {
        return $this->_armies[$armyId];
    }

    public function toArray()
    {
        $armies = array();
        foreach ($this->_armies as $armyId => $army) {
            $armies[$armyId] = $army->toArray();
        }
        return $armies;
    }

//    public function sameTeam($color1, $color2)
//    {
//        if ($color1 == $color2) {
//            return true;
//        }
//        return $this->getArmy($color1)->getTeam() == $this->getArmy($color2)->getTeam();
//    }
}