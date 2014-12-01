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
            foreach ($army->getSoldiers() as $soldierId => $soldier) {
                if (!isset($soldiersMovesLeft[$soldierId])) {
                    $soldiersMovesLeft[$soldierId] = $soldier->getMovesLeft();
                }

                $soldiersMovesLeft[$soldierId] -= $soldier->getStepCost($step['tt'], $type);

                if ($soldiersMovesLeft[$soldierId] < 0) {
                    $skip = true;
                }

                if ($soldiersMovesLeft[$soldierId] <= 0) {
                    $stop = true;
                    break;
                }
            }

            foreach ($army->getShips() as $soldierId => $soldier) {
                if (!isset($soldiersMovesLeft[$soldierId])) {
                    $soldiersMovesLeft[$soldierId] = $soldier->getMovesLeft();
                }

                $soldiersMovesLeft[$soldierId] -= $soldier->getStepCost($step['tt'], $type);

                if ($soldiersMovesLeft[$soldierId] < 0) {
                    $skip = true;
                }

                if ($soldiersMovesLeft[$soldierId] <= 0) {
                    $stop = true;
                    break;
                }
            }

            foreach ($army->getHeroes() as $heroId => $hero) {
                if (!isset($heroesMovesLeft[$heroId])) {
                    $heroesMovesLeft[$heroId] = $hero->getMovesLeft();
                }

                if (!isset($step['cc'])) {
                    $heroesMovesLeft[$heroId] -= $heroId->getStepCost($step['tt'], $type);
                }

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

            if (isset($step['cc'])) {
                $this->_current[] = array(
                    'x' => $step['x'],
                    'y' => $step['y'],
                    'tt' => $step['tt'],
                    'myCastleCosts' => true
                );
            } else {
                $this->_current[] = array(
                    'x' => $step['x'],
                    'y' => $step['y'],
                    'tt' => $step['tt']
                );
            }

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
}