<?php

class Cli_Model_Tower
{
    public $_id;
    public $x;
    public $y;

    public function __construct($tower)
    {
        $this->_id = $tower['towerId'];
        $this->x = $tower['x'];
        $this->y = $tower['y'];
    }

    public function toArray()
    {
        return array(
            'x' => $this->x,
            'y' => $this->y,
        );
    }
}
