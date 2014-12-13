<?php

class Cli_Model_Garrison
{
    private $_garrison;
    private $_countGarrisonArmies = 0;
    private $_countGarrisonUnits = 0;
    private $_armiesToGo;
    private $_countArmiesToGo = 0;

    public function __construct($x, $y, Cli_Model_Armies $armies)
    {
        $this->_garrison = new Cli_Model_Armies();
        $this->_armiesToGo = new Cli_Model_Armies();
        foreach ($armies->getKeys() as $armyId) {
            $army = $armies->getArmy($armyId);
            for ($i = $x; $i <= $x + 1; $i++) {
                for ($j = $y; $j <= $y + 1; $j++) {
                    if ($army->getX() == $i && $army->getY() == $j) {
                        $this->_countGarrisonArmies = $army->count();
                        $this->_garrison->addArmy($armyId, $army);
                    }
                }
            }
        }
    }

    /**
     * @param $numberOfUnits
     * @return Cli_Model_Army
     * @throws Exception
     */
    public function sufficient($numberOfUnits)
    {
        if ($this->_countGarrisonArmies > $numberOfUnits) {
            $count = 0;
            foreach ($this->_garrison->getKeys() as $armyId) {
                $army = $this->_garrison->getArmy($armyId);
                if ($count >= $numberOfUnits) {
                    $this->_garrison->removeArmy($armyId);
                    $this->_armiesToGo->addArmy($armyId, $army);
                    $this->_countArmiesToGo++;
                } else {
                    $this->_countGarrisonArmies++;
                    $count += $army->count();
                }
            }
        }
    }

    public function getCountGarrisonArmies()
    {
        return $this->_countGarrisonArmies;
    }

    public function getCountArmiesToGo()
    {
        return $this->_countArmiesToGo;
    }

    public function getKeys()
    {
        return $this->_garrison->getKeys();
    }

    public function getArmy($armyId)
    {
        return $this->_garrison->getArmy($armyId);
    }

    public function fortify($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        foreach ($this->_garrison->getKeys() as $armyId) {
            $this->_garrison->getArmy($armyId)->setFortified(true, $gameId, $db);
        }
    }

    public function getArmyToGo()
    {
        reset($this->_armiesToGo);
        return current($this->_armiesToGo);
    }

    public function getNextArmyToGo()
    {
        return next($this->_armiesToGo);
    }
}
