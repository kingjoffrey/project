<?php

class Cli_Model_TerrainTypes
{
    private $_movingTypes;

    public function __construct($terrain)
    {
        foreach ($terrain as $row) {
            $this->_movingTypes[$row['type']] = new Cli_Model_TerrainCosts($row);
        }
    }

    public function toArray()
    {
        $types = array();
        foreach (array_keys($this->_movingTypes) as $movingType) {
            $costs = $this->getTerrainType($movingType);
            $types[$movingType] = $costs->toArray();
        }
        return $types;
    }

    /**
     * @param $terrainType
     * @return Cli_Model_TerrainCosts
     */
    public function getTerrainType($terrainType)
    {
        return $this->_movingTypes[$terrainType];
    }
}
