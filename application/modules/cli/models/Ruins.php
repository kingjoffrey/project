<?php

class Cli_Model_Ruins
{
    protected $_ruins = array();

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
}
