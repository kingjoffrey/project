<?php

class Cli_Model_Ruin extends Cli_Model_Entity
{
    protected $_empty;
    protected $_type;

    public function __construct($ruin, $empty = true)
    {
        $this->_id = $ruin['mapRuinId'];
        $this->_x = $ruin['x'];
        $this->_y = $ruin['y'];
        $this->_empty = $empty;
        $this->_type = $ruin['ruinId'];
    }

    public function toArray()
    {
        return array(
            'empty' => $this->_empty,
            'x' => $this->_x,
            'y' => $this->_y,
            'type' => $this->_type
        );
    }
}
