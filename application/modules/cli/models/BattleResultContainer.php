<?php

class Cli_Model_BattleResultContainer
{
    private $_container = array();

    public function toArray()
    {
        return $this->_container;
    }

    public function addSuccession($id, $succession)
    {
        $this->_container[$id] = $succession;
    }

    public function add($id)
    {
        if (isset($this->_container[$id])) {
            return true;
        } else {
            $this->_container[$id] = null;
        }
    }

    public function isDead($id)
    {
        if (isset($this->_container[$id])) {
            return true;
        }
    }
}