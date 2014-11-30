<?php

class Cli_Model_Towers
{
    private $_towers = array();

    public function toArray()
    {
        $towers = array();
        foreach ($this->_towers as $towerId => $tower) {
            $towers[$towerId] = $tower->toArray();
        }
        return $towers;
    }

    public function get()
    {
        return $this->_towers;
    }

    public function add($towerId, Cli_Model_Tower $tower)
    {
        $this->_towers[$towerId] = $tower;
    }

    /**
     * @param $towerId
     * @return Cli_Model_Tower
     */
    public function getTower($towerId)
    {
        return $this->_towers[$towerId];
    }
}
