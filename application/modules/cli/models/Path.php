<?php

class Cli_Model_Path
{
    public $current;
    public $full;
    public $end;
    public $x;
    public $y;
    public $castleId = null;
    public $ruinId = null;
    public $in = false;

    public function __construct($current = null, $full = null)
    {
        if (is_array($current)) {
            $this->full = $full;
            $this->current = $current;
            $this->end = end($current);
            $this->x = $this->end['x'];
            $this->y = $this->end['y'];
        }
    }
}