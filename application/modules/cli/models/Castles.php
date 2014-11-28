<?php

class Cli_Model_Castles
{
    private $_castles = array();

    public function get()
    {
        return $this->_castles;
    }

    public function addCastle($castleId, $castle)
    {
        $this->_castles[$castleId] = $castle;
    }

    /**
     * @param $castleId
     * @return Cli_Model_Castle
     */
    public function getCastle($castleId)
    {
        return $this->_castles[$castleId];
    }

    public function toArray()
    {
        $castles = array();
        foreach ($this->_castles as $castleId => $castle) {
            $castles[$castleId] = $castle->toArray();
        }
        return $castles;
    }

//    public function sameTeam($color1, $color2)
//    {
//        if ($color1 == $color2) {
//            return true;
//        }
//        return $this->getArmy($color1)->getTeam() == $this->getArmy($color2)->getTeam();
//    }
}