<?php

abstract class Cli_Model_Entity
{
    protected $_id;
    protected $_x;
    protected $_y;

    public function getId()
    {
        return $this->_id;
    }

    public function getX()
    {
        return $this->_x;
    }

    public function getY()
    {
        return $this->_y;
    }
}
