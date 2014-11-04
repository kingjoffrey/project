<?php

class Cli_Model_Castle
{
    public $x;
    public $y;

    private $_production;
    private $_relocation;

    public function __construct($castle)
    {
        $this->x = $castle['x'];
        $this->y = $castle['y'];
        $this->_production = $castle['production'];
        $this->_relocation = $castle['relocation'];
    }

    public function toArray()
    {
        return array(
            'x' => $this->x,
            'y' => $this->y,
            'production' => $this->_production
        );
    }
}