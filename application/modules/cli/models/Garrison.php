<?php

class Cli_Model_Garrison
{
    private $_garrison;
    private $_count;

    public function __construct($x, $y, Cli_Model_Armies $armies)
    {
        $this->_garrison = new Cli_Model_Armies();
        foreach ($armies->getKeys() as $armyId) {
            $army = $armies->getArmy($armyId);
            for ($i = $x; $i <= $x + 1; $i++) {
                for ($j = $y; $j <= $y + 1; $j++) {
                    if ($army->getX() == $i && $army->getY() == $j) {
                        $this->_count = $army->count();
                        $this->_garrison->addArmy($armyId, $army);
                    }
                }
            }
        }
    }

    public function sufficient($numberOfUnits)
    {
        if ($this->_count > $numberOfUnits) {
            $count = 0;
            foreach ($this->_garrison->getKeys() as $armyId) {
                $army = $this->_garrison->getArmy($armyId);
                if ($count >= $numberOfUnits) {
                    return $army;
                }
                $count += $army->count();
            }
        }
    }

    public function getCount()
    {
        return $this->_count;
    }
}
