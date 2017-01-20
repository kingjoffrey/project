<?php

class Cli_Model_Units
{
    private $_units;
    private $_specialIds;

    public function toArray()
    {
        $units = array();
        foreach ($this->_units as $unitId => $unit) {
            $units[$unitId] = $unit->toArray();
        }
        return $units;
    }

    public function get()
    {
        return $this->_units;
    }

    public function getKeys()
    {
        return array_keys($this->_units);
    }

    public function add($unitId, Cli_Model_Unit $unit)
    {
        $this->_units[$unitId] = $unit;
    }

    /**
     * @param $unitId
     * @return Cli_Model_Unit
     */
    public function getUnit($unitId)
    {
        return $this->_units[$unitId];
    }

    public function getFirstUnitId()
    {
        reset($this->_units);
        return key($this->_units);
    }

    public function initSpecial()
    {
        if (!$this->_specialIds) {
            foreach ($this->_units as $unitId => $unit) {
                if ($unit->getSpecial()) {
                    $this->_specialIds[] = $unitId;
                }
            }
        }
    }

    public function countSpecialUnits()
    {
        $this->initSpecial();
        return count($this->_specialIds);
    }

    public function getSpecialUnitId($key)
    {
        $this->initSpecial();
        return $this->_specialIds[$key];
    }

    public function getDragonId()
    {
        foreach ($this->_units as $unitId => $unit) {
            if ($unit->getSpecial()) {
                if ($unit->getName() == 'Dragon') {
                    return $unit->getId();
                }
            }
        }
    }
}
