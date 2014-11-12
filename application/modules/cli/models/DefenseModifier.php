<?php

/**
 * Class Cli_Model_DefenseModifier
 * ver. 0002
 */
class Cli_Model_DefenseModifier
{
    private $_mod = 0;

    public function getMod()
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

    public function add($defense)
    {
        $this->_mod += $defense;
    }
}