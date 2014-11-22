<?php

class Cli_Model_Tower
{
    private $_id;
    private $_x;
    private $_y;

    public function __construct($tower)
    {
        $this->_id = $tower['towerId'];
        $this->_x = $tower['x'];
        $this->_y = $tower['y'];
    }

    public function toArray()
    {
        return array(
            'x' => $this->_x,
            'y' => $this->_y,
        );
    }

    public function getX()
    {
        return $this->_x;
    }

    public function getY()
    {
        return $this->_y;
    }

    public function getId()
    {
        return $this->_id;
    }
}
