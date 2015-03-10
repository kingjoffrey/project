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
        echo "\n";
        echo '***                       PATH                             !!!' . "\n";
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

        echo '      ' . $type . "\n";

        foreach ($this->_full as $step) {
            echo 'step[t]= ' . $step['t'] . "\n";
            if (isset($step['cc'])) {
                $this->_current[] = array(
                    'x' => $step['x'],
                    'y' => $step['y'],
                    't' => $step['t'],
                    'c' => true
                );
                continue;
            }

            foreach ($army->getSoldiers()->getKeys() as $soldierId) {
                $soldier = $army->getSoldiers()->getSoldier($soldierId);
                if (!isset($soldiersMovesLeft[$soldierId])) {
                    $soldiersMovesLeft[$soldierId] = $soldier->getMovesLeft();
                    echo 'FIRST             $soldiersMovesLeft=    ' . $soldiersMovesLeft[$soldierId] . "\n";
                }

                $soldiersMovesLeft[$soldierId] -= $soldier->getStepCost($terrain, $step['t'], $type);
                echo '$soldiersMovesLeft= ' . $soldiersMovesLeft[$soldierId] . "\n";

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

                $soldiersMovesLeft[$soldierId] -= $soldier->getStepCost($terrain, $step['t'], $type);

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

                $heroesMovesLeft[$heroId] -= $terrain->getTerrainType($step['t'])->getCost($type);

                if ($heroesMovesLeft[$heroId] < 0) {
                    $skip = true;
                }

                if ($heroesMovesLeft[$heroId] <= 0) {
                    $stop = true;
                    break;
                }
            }

            if ($skip) {
                echo 'skip' . "\n";
                break;
            }

            $this->_current[] = array(
                'x' => $step['x'],
                'y' => $step['y'],
                't' => $step['t'],
                'c' => false
            );

            if ($step['t'] == 'E') {
                echo 'E - break' . "\n";
                break;
            }

            if ($stop) {
                echo 'stop' . "\n";
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
        return $this->_end['t'] == 'E';
    }

    public function targetWithin()
    {
        return $this->exists() && count($this->_current) == count($this->_full);
    }

    public function getFull()
    {
        return $this->_full;
    }

    public function unitsHaveRange($fullPath)
    {
        $soldiersMovesLeft = array();
        $heroesMovesLeft = array();

        foreach ($this->_soldiers as $soldierId => $soldier) {
            // ustawiam początkową ilość ruchów dla każdej jednostki
            if (!isset($soldiersMovesLeft[$soldierId])) {
                $soldiersMovesLeft[$soldierId] = $this->_units[$soldier['unitId']]['numberOfMoves'];
                if ($soldier->getMovesLeft() <= 2) {
                    $soldiersMovesLeft[$soldierId] += $soldier->getMovesLeft();
                } else {
                    $soldiersMovesLeft[$soldierId] += 2;
                }
            }

            foreach ($fullPath as $step) {
                // odejmuję
                if ($step['t'] == 'f') {
                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesForest'];
                } elseif ($step['t'] == 's') {
                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesSwamp'];
                } elseif ($step['t'] == 'm') {
                    $soldiersMovesLeft[$soldierId] -= $this->_units[$soldier['unitId']]['modMovesHills'];
                } else {
                    if ($this->_units[$soldier['unitId']]['canFly']) {
                        $soldiersMovesLeft[$soldierId] -= $this->_terrain[$step['t']]['flying'];
                    } elseif ($this->_units[$soldier['unitId']]['canSwim']) {
                        $soldiersMovesLeft[$soldierId] -= $this->_terrain[$step['t']]['swimming'];
                    } else {
                        $soldiersMovesLeft[$soldierId] -= $this->_terrain[$step['t']]['walking'];

                    }
                }

                if ($step['t'] == 'E') {
                    break;
                }

                if ($soldiersMovesLeft[$soldierId] <= 0) {
                    break;
                }
            }
        }

        foreach ($this->_heroes as $heroId => $hero) {
            if (!isset($heroesMovesLeft[$heroId])) {
                $heroesMovesLeft[$heroId] = $hero['numberOfMoves'];
                if ($hero->getMovesLeft() <= 2) {
                    $heroesMovesLeft[$heroId] += $hero->getMovesLeft();
                } elseif ($hero->getMovesLeft() > 2) {
                    $heroesMovesLeft[$heroId] += 2;
                }
            }

            foreach ($fullPath as $step) {
                $heroesMovesLeft[$heroId] -= $this->_terrain[$step['t']]['walking'];

                if ($step['t'] == 'E') {
                    break;
                }

                if ($heroesMovesLeft[$heroId] <= 0) {
                    break;
                }
            }
        }


        foreach ($soldiersMovesLeft as $s) {
            if ($s >= 0) {
                return true;
            }
        }

        foreach ($heroesMovesLeft as $h) {
            if ($h >= 0) {
                return true;
            }
        }
    }

    public function rewind()
    {

    }
}