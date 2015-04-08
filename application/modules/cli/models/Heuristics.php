<?php

class Cli_Model_Heuristics
{

    /**
     * Destination x value
     *
     * @var int
     */
    protected $destX;

    /**
     * Destination y value
     *
     * @var int
     */
    protected $destY;

    public function __construct($destX, $destY)
    {
        $this->destX = $destX;
        $this->destY = $destY;
    }

    /**
     * Calculates heuristic estimate
     *
     * @param int $x
     * @param int $y
     * @return int
     */
    public function calculateH($x, $y)
    {
        return sqrt(pow($this->destX - $x, 2) + pow($y - $this->destY, 2));
    }

    public function calculateWithFieldsCosts($x, $y, $fields)
    {

    }
}