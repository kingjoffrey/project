<?php

class Cli_Model_TerrainCosts
{
    private $_name;
    private $_costs = array();

    public function __construct($row)
    {
        $this->_costs['fly'] = $row['flying'];
        $this->_costs['walk'] = $row['walking'];
        $this->_costs['swim'] = $row['swimming'];
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
        echo 'getCost type=' . $type . ' costs[type]=' . $this->_costs[$type] . "\n";
        return $this->_costs[$type];
    }

    public function getNme()
    {
        return $this->_name;
    }
}
