<?php

/**
 * Class Cli_Model_BattleModifier
 * ver. 0001
 */
class Cli_Model_BattleModifier
{
    private $_mod = 0;

    public function add($mod)
    {
        $this->_mod += $mod;
    }

    public function get()
    {
        if ($this->_mod > 0) {
            return 1;
        } elseif ($this->_mod == 0) {
            return 0;
        } else {
            throw new Exception('error 0012');
        }
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