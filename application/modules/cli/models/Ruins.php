<?php

class Cli_Model_Ruins
{
    private $_ruins;

    public function toArray()
    {
        $ruins = array();
        foreach ($this->_ruins as $ruinId => $ruin) {
            $ruins[$ruinId] = $ruin->toArray();
        }
        return $ruins;
    }

    public function get()
    {
        return $this->_ruins;
    }

    public function getKeys()
    {
        return array_keys($this->_ruins);
    }

    public function add($ruinId, Cli_Model_Ruin $ruin)
    {
        $this->_ruins[$ruinId] = $ruin;
    }

    /**
     * @param $ruinId
     * @return Cli_Model_Ruin
     */
    public function getRuin($ruinId)
    {
        return $this->_ruins[$ruinId];
    }
}
