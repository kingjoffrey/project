<?php

class Cli_Model_Tower extends Cli_Model_Entity
{
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
}
