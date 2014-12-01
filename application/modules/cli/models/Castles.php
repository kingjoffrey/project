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
        if ($this->hasCastle($castleId)) {
            return $this->_castles[$castleId];
        }
    }

    public function toArray()
    {
        $castles = array();
        foreach ($this->_castles as $castleId => $castle) {
            $castles[$castleId] = $castle->toArray();
        }
        return $castles;
    }

    public function hasCastle($castleId)
    {
        return isset($this->_castles[$castleId]);
    }
}