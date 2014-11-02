<?php

class Cli_Model_Ruin
{
    public $empty;

    public $x;

    public $y;

    public function __construct($position, $empty)
    {
        $this->empty = $empty;
        $this->x = $position['x'];
        $this->y = $position['y'];
    }
}
