<?php

class Cli_Model_Path
{
    private $_currentPath = array();
    private $_currentPathEnd;
    private $_currentDestinationX;
    private $_currentDestinationY;

    private $_fullPath;
    private $_fullDestinationX;
    private $_fullDestinationY;

    private $_terrain;
    private $_army;
    private $_movementType;

    public function __construct($fullPath, Cli_Model_Army $army, Cli_Model_TerrainTypes $terrain)
    {
        if (empty($fullPath)) {
            return;
        }

        $this->_fullPath = $fullPath;
        $this->_terrain = $terrain;
        $this->_army = $army;

        $this->_movementType = $this->_army->getMovementType();

        $this->computeCurrentPath(false);
    }

    public function computeCurrentPath($pretend)
    {
        $destination = end($this->_fullPath);
        $this->_fullDestinationX = $destination['x'];
        $this->_fullDestinationY = $destination['y'];

        $skip = null;
        $stop = null;

        foreach ($this->_fullPath as $key => $step) {
            if (isset($step['cc'])) {
                continue;
            }

            switch ($this->_movementType) {
                case 'fly':
                    foreach ($this->_army->getFlyingSoldiers()->getKeys() as $soldierId) {
                        $soldier = $this->_army->getFlyingSoldiers()->getSoldier($soldierId);
                        if (!isset($soldiersMovesLeft[$soldierId])) {
                            if ($pretend) {
                                $soldiersMovesLeft[$soldierId] = $soldier->getMoves();
                            } else {
                                $soldiersMovesLeft[$soldierId] = $soldier->getMovesLeft();
                            }
                        }

                        $soldiersMovesLeft[$soldierId] -= $soldier->getStepCost($this->_terrain, $step['t'], $this->_movementType);

                        if ($soldiersMovesLeft[$soldierId] < 0) {
                            if ($skip === null) {
                                $skip = $key;
                            }
                        }

                        if ($soldiersMovesLeft[$soldierId] <= 0) {
                            if ($stop === null) {
                                $stop = $key;
                            }
                        }
                    }
                    break;
                case 'swim':
                    foreach ($this->_army->getSwimmingSoldiers()->getKeys() as $soldierId) {
                        $soldier = $this->_army->getSwimmingSoldiers()->getSoldier($soldierId);
                        if (!isset($soldiersMovesLeft[$soldierId])) {
                            if ($pretend) {
                                $soldiersMovesLeft[$soldierId] = $soldier->getMoves();
                            } else {
                                $soldiersMovesLeft[$soldierId] = $soldier->getMovesLeft();
                            }
                        }

                        $soldiersMovesLeft[$soldierId] -= $soldier->getStepCost($this->_terrain, $step['t'], $this->_movementType);

                        if ($soldiersMovesLeft[$soldierId] < 0) {
                            if ($skip === null) {
                                $skip = $key;
                            }
                        }

                        if ($soldiersMovesLeft[$soldierId] <= 0) {
                            if ($stop === null) {
                                $stop = $key;
                            }
                        }
                    }
                    break;
                case 'walk':
                    foreach ($this->_army->getFlyingSoldiers()->getKeys() as $soldierId) {
                        $soldier = $this->_army->getFlyingSoldiers()->getSoldier($soldierId);
                        if (!isset($soldiersMovesLeft[$soldierId])) {
                            if ($pretend) {
                                $soldiersMovesLeft[$soldierId] = $soldier->getMoves();
                            } else {
                                $soldiersMovesLeft[$soldierId] = $soldier->getMovesLeft();
                            }
                        }

                        $soldiersMovesLeft[$soldierId] -= $soldier->getStepCost($this->_terrain, $step['t'], $this->_movementType);

                        if ($soldiersMovesLeft[$soldierId] < 0) {
                            if ($skip === null) {
                                $skip = $key;
                            }
                        }

                        if ($soldiersMovesLeft[$soldierId] <= 0) {
                            if ($stop === null) {
                                $stop = $key;
                            }
                        }
                    }

                    foreach ($this->_army->getWalkingSoldiers()->getKeys() as $soldierId) {
                        $soldier = $this->_army->getWalkingSoldiers()->getSoldier($soldierId);
                        if (!isset($soldiersMovesLeft[$soldierId])) {
                            $soldiersMovesLeft[$soldierId] = $soldier->getMovesLeft();
                        }

                        $soldiersMovesLeft[$soldierId] -= $soldier->getStepCost($this->_terrain, $step['t'], $this->_movementType);

                        if ($soldiersMovesLeft[$soldierId] < 0) {
                            if ($skip === null) {
                                $skip = $key;
                            }
                        }

                        if ($soldiersMovesLeft[$soldierId] <= 0) {
                            if ($stop === null) {
                                $stop = $key;
                            }
                        }
                    }

                    foreach ($this->_army->getHeroes()->getKeys() as $heroId) {
                        $hero = $this->_army->getHeroes()->getHero($heroId);
                        if (!isset($heroesMovesLeft[$heroId])) {
                            if ($pretend) {
                                $heroesMovesLeft[$heroId] = $hero->getMoves();
                            } else {
                                $heroesMovesLeft[$heroId] = $hero->getMovesLeft();
                            }
                        }

                        $heroesMovesLeft[$heroId] -= $hero->getStepCost($this->_terrain, $step['t'], $this->_movementType);

                        if ($heroesMovesLeft[$heroId] < 0) {
                            if ($skip === null || $key < $skip) {
                                $skip = $key;
                            }
                        }

                        if ($heroesMovesLeft[$heroId] <= 0) {
                            if ($stop === null || $key < $stop) {
                                $stop = $key;
                            }
                        }
                    }
                    break;
                default:
                    throw new Exception('nieznany typ ruchu!');
            }
        }

        foreach ($this->_fullPath as $key => $step) {
            if (isset($step['cc'])) {
                $this->_currentPath[] = array(
                    'x' => $step['x'],
                    'y' => $step['y'],
                    't' => $step['t'],
                    'c' => true
                );
                continue;
            }

            if ($skip === $key) {
                break;
            }

            $this->_currentPath[] = array(
                'x' => $step['x'],
                'y' => $step['y'],
                't' => $step['t'],
                'c' => false
            );

            if ($step['t'] == 'E') {
                break;
            }

            if ($stop === $key) {
                break;
            }
        }

        $this->_currentPathEnd = end($this->_currentPath);
        $this->_currentDestinationX = $this->_currentPathEnd['x'];
        $this->_currentDestinationY = $this->_currentPathEnd['y'];
    }

    public function getCurrentDestinationX()
    {
        return $this->_currentDestinationX;
    }

    public function getCurrentDestinationY()
    {
        return $this->_currentDestinationY;
    }

    public function getCurrentPath()
    {
        return $this->_currentPath;
    }

    public function getCurrentPathEnd()
    {
        return $this->_currentPathEnd;
    }

    public function exists()
    {
        return count($this->_currentPath);
    }

    public function enemyInRange()
    {
        return $this->_currentPathEnd['t'] == 'E';
    }

    public function targetWithin()
    {
        return $this->exists() && count($this->_currentPath) == count($this->_fullPath);
    }

    public function setCurrentPath($current)
    {
        $this->_currentPath = $current;
        $this->_currentPathEnd = end($this->_currentPath);
        $this->_currentDestinationX = $this->_currentPathEnd['x'];
        $this->_currentDestinationY = $this->_currentPathEnd['y'];
    }

    public function cutCurrentPathFromFull()
    {

    }

    public function getFullPath()
    {
        return $this->_fullPath;
    }

    public function getFullDestinationX()
    {
        return $this->_fullDestinationX;
    }

    public function getFullDestinationY()
    {
        return $this->_fullDestinationY;
    }
}
