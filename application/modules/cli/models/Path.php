<?php

class Cli_Model_Path
{
    private $_current = array();
    private $_full;
    private $_end;
    private $_x;
    private $_y;

    public function __construct($fullPath, Cli_Model_Army $army)
    {
        if (empty($fullPath)) {
            return $this;
        }

        $terrain = Zend_Registry::get('terrain');
        $this->_full = $fullPath;
        $skip = false;
        $stop = false;

        if ($army->canFly()) {
            $type = 'flying';
        } elseif ($army->canSwim()) {
            $type = 'swimming';
        } else {
            $type = 'walking';
        }

        foreach ($this->_full as $step) {
            foreach ($army->getSoldiers()->getKeys() as $soldierId) {
                $soldier = $army->getSoldiers()->getSoldier($soldierId);
                if (!isset($soldiersMovesLeft[$soldierId])) {
                    $soldiersMovesLeft[$soldierId] = $soldier->getMovesLeft();
                }

                if (isset($step['cc'])) {
                    continue;
                }

                $soldiersMovesLeft[$soldierId] -= $soldier->getStepCost($terrain, $step['tt'], $type);

                if ($soldiersMovesLeft[$soldierId] < 0) {
                    $skip = true;
                }

                if ($soldiersMovesLeft[$soldierId] <= 0) {
                    $stop = true;
                    break;
                }
            }

            foreach ($army->getShips()->getKeys() as $soldierId) {
                $soldier = $army->getShips()->getSoldier($soldierId);
                if (!isset($soldiersMovesLeft[$soldierId])) {
                    $soldiersMovesLeft[$soldierId] = $soldier->getMovesLeft();
                }

                if (isset($step['cc'])) {
                    continue;
                }

                $soldiersMovesLeft[$soldierId] -= $soldier->getStepCost($terrain, $step['tt'], $type);

                if ($soldiersMovesLeft[$soldierId] < 0) {
                    $skip = true;
                }

                if ($soldiersMovesLeft[$soldierId] <= 0) {
                    $stop = true;
                    break;
                }
            }

            foreach ($army->getHeroes()->getKeys() as $heroId) {
                if (!isset($heroesMovesLeft[$heroId])) {
                    $heroesMovesLeft[$heroId] = $army->getHeroes()->getHero($heroId)->getMovesLeft();
                }

                if (isset($step['cc'])) {
                    continue;
                }

                $heroesMovesLeft[$heroId] -= $terrain[$step['tt']][$type];

                if ($heroesMovesLeft[$heroId] < 0) {
                    $skip = true;
                }

                if ($heroesMovesLeft[$heroId] <= 0) {
                    $stop = true;
                    break;
                }
            }

            if ($skip) {
                break;
            }

            $this->_current[] = $step;

            if ($step['tt'] == 'E') {
                break;
            }

            if ($stop) {
                break;
            }
        }

        $this->_end = end($this->_current);
        $this->_x = $this->_end['x'];
        $this->_y = $this->_end['y'];
    }

    public function getX()
    {
        return $this->_x;
    }

    public function getY()
    {
        return $this->_y;
    }

    public function getCurrent()
    {
        return $this->_current;
    }

    public function getEnd()
    {
        return $this->_end;
    }

    public function exists()
    {
        return count($this->_current);
    }

    public function enemyInRange()
    {
        return $this->_end['tt'] == 'E';
    }
}