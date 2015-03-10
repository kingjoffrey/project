<?php

class Cli_Model_TerrainCosts
{
    private $_name;
    private $_costs = array();

    public function __construct($row)
    {
        $this->_costs['flying'] = $row['flying'];
        $this->_costs['walking'] = $row['walking'];
        $this->_costs['swimming'] = $row['swimming'];
        $this->_name = $row['name'];
    }

    public function toArray()
    {
        $costs = $this->_costs;
        $costs['name'] = $this->_name;
        return $costs;
    }

    public function getCost($type)
    {
        $this->_costs[$type];
    }

    public function getNme()
    {
        return $this->_name;
    }
}
