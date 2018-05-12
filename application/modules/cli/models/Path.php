<?php

class Cli_Model_Path
{
    private $_currentPath;
    private $_currentPathEnd;

    private $_fullPath;
    private $_fullDestinationX;
    private $_fullDestinationY;

    private $_cutFullPath = null;

    private $_tmpCurrentPathEnd;

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

        $destination = end($fullPath);
        $this->_fullDestinationX = $destination['x'];
        $this->_fullDestinationY = $destination['y'];

        $this->computeCurrentPath(false);
    }

    public function computeCurrentPath($resetMovesLeft)
    {
        $skip = null;
        $stop = null;

        $currentPath = array();

        if ($resetMovesLeft) {
            $fullPath = $this->_cutFullPath;
        } else {
            $fullPath = $this->_fullPath;
        }


        foreach ($fullPath as $key => $step) {
            if (isset($step['cc'])) {
                continue;
            }

            switch ($this->_movementType) {
                case 'fly':
                    foreach ($this->_army->getFlyingSoldiers()->getKeys() as $soldierId) {
                        $soldier = $this->_army->getFlyingSoldiers()->getSoldier($soldierId);
                        if (!isset($soldiersMovesLeft[$soldierId])) {
                            if ($resetMovesLeft) {
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
                            if ($resetMovesLeft) {
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
                            if ($resetMovesLeft) {
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
                            if ($resetMovesLeft) {
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

                    foreach ($this->_army->getHeroes()->getKeys() as $heroId) {
                        $hero = $this->_army->getHeroes()->getHero($heroId);
                        if (!isset($heroesMovesLeft[$heroId])) {
                            if ($resetMovesLeft) {
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

        foreach ($fullPath as $key => $step) {
            if (isset($step['cc'])) {
                $currentPath[] = array(
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

            $currentPath[] = array(
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

        $this->_tmpCurrentPathEnd = end($currentPath);

        if (!$resetMovesLeft) {
            $this->_currentPath = $currentPath;
            $this->_currentPathEnd = $this->_tmpCurrentPathEnd;
        }
    }

    public function getTmpCurrentDestinationX()
    {
        return $this->_tmpCurrentPathEnd['x'];
    }

    public function getTmpCurrentDestinationY()
    {
        return $this->_tmpCurrentPathEnd['y'];
    }

    public function getCurrentDestinationX()
    {
        return $this->_currentPathEnd['x'];
    }

    public function getCurrentDestinationY()
    {
        return $this->_currentPathEnd['y'];
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

    public function enemyInTmpRange()
    {
        return $this->_tmpCurrentPathEnd['t'] == 'E';
    }

    public function targetWithin()
    {
        return $this->exists() && count($this->_currentPath) == count($this->_fullPath);
    }

    public function setCurrentPath($current)
    {
        $this->_currentPath = $current;
        $this->_currentPathEnd = end($current);
    }

    public function removeCurrentPathFromFull()
    {
        $cutFullPath = array();
        $start = false;
        $currentDestinationX = $this->_tmpCurrentPathEnd['x'];
        $currentDestinationY = $this->_tmpCurrentPathEnd['y'];

        if (!$this->_cutFullPath) {
            $this->_cutFullPath = $this->_fullPath;
        }

        foreach ($this->_cutFullPath as $step) {
            if ($start) {
                $cutFullPath[] = $step;
            }

            if ($step['x'] == $currentDestinationX && $step['y'] == $currentDestinationY) {
                $start = true;
            }
        }

        $this->_cutFullPath = $cutFullPath;
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
