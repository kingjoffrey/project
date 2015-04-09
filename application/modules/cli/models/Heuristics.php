<?php

class Cli_Model_Heuristics
{

    /**
     * Destination x value
     *
     * @var int
     */
    protected $_destX;

    /**
     * Destination y value
     *
     * @var int
     */
    protected $_destY;

    public function __construct($destX, $destY)
    {
        $this->_destX = $destX;
        $this->_destY = $destY;
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
        return sqrt(pow($this->_destX - $x, 2) + pow($y - $this->_destY, 2));
    }

    public function calculateWithFieldsCosts($x, $y, Cli_Model_Fields $fields, Cli_Model_TerrainTypes $terrainTypes)
    {
        if ($x > $this->_destX && $y > $this->_destY) {
            return $this->getHeuristicsWithFieldsCosts($x, $this->_destX, $y, $this->_destY, $fields, $terrainTypes);
        } elseif ($x > $this->_destX && $this->_destY > $y) {
            return $this->getHeuristicsWithFieldsCosts($x, $this->_destX, $this->_destY, $y, $fields, $terrainTypes);
        } elseif ($this->_destX > $x && $this->_destY > $y) {
            return $this->getHeuristicsWithFieldsCosts($this->_destX, $x, $this->_destY, $y, $fields, $terrainTypes);
        } elseif ($this->_destX > $x && $y > $this->_destY) {
            return $this->getHeuristicsWithFieldsCosts($this->_destX, $x, $y, $this->_destY, $fields, $terrainTypes);
        }
    }

    private function getHeuristicsWithFieldsCosts($x1, $x2, $y1, $y2, Cli_Model_Fields $fields, Cli_Model_TerrainTypes $terrainTypes)
    {
        $h = 0;
        $xLength = abs($x1 - $x2);
        $yLength = abs($y1 - $y2);
        if ($xLength > $yLength) {
            $iterations = $xLength;
        } else {
            $iterations = $yLength;
        }

        for ($i = 0; $i < $iterations; $i++) {
            $newX = $x2 + $i;
            if ($newX > $x1) {
                $newX = $x1;
            }
            $newY = $y2 + $i;
            if ($newY > $y1) {
                $newY = $y1;
            }
            $h += $terrainTypes->getTerrainType($fields->getField($newX, $newY)->getType())->getCost('walk');
        }
        return $h;
    }
}