<?php

/**
 * Class Cli_Model_AttackModifier
 * ver. 0001
 */
class Cli_Model_AttackModifier
{
    private $_mod = 0;

    public function getMod()
    {
        return $this->_mod;
    }

    public function increment(){
        $this->_mod++;
    }

    public function decrement(){
        $this->_mod--;
    }
}