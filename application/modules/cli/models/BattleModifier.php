<?php

/**
 * Class Cli_Model_BattleModifier
 * ver. 0001
 */
class Cli_Model_BattleModifier
{
    private $_mod = 0;

    public function set($mod)
    {
        $this->_mod = $mod;
    }

    public function add($mod)
    {
        $this->_mod += $mod;
    }

    public function get()
    {
        return $this->_mod;
    }

    public function increment()
    {
        $this->_mod++;
    }

    public function decrement()
    {
        $this->_mod--;
    }
}