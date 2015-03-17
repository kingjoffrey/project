<?php

class Cli_Model_Units
{
    private $_units;
    private $_special;

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

    public function getSpecialUnitId($key)
    {
        if (!$this->_special) {
            foreach ($this->_units as $unitId => $unit) {
                if ($unit->getSpecial()) {
                    $this->_special[] = $unitId;
                }
            }
        }
        return $this->_special[$key];
    }
}
