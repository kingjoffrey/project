<?php

class Cli_Model_Garrison
{
    private $_newArmyId = 0;

    public function __construct($x, $y, $color, Cli_Model_Armies $armies, Cli_Model_Game $game, $handler)
    {
        $gameId = $game->getId();
        $fields = $game->getFields();
        $db = $handler->getDb();

        for ($i = $x; $i <= $x + 1; $i++) {
            for ($j = $y; $j <= $y + 1; $j++) {
                if ($i != $x || $j != $y) {
                    foreach ($fields->getField($i, $j)->getArmies() as $fieldArmyId => $fieldArmyColor) {
//                        echo 'aaa ';
                        if ($fieldArmyColor == $color) {
//                            echo 'fff';
                            $army = $armies->getArmy($fieldArmyId);
                            $path = new Cli_Model_Path(array(0 => array(
                                'x' => $x,
                                'y' => $y,
                                't' => 'c')
                            ), $army, $game->getTerrain());
                            $army->move($game, $path, $handler);
                        }
                    }
                }
            }
        }

        $moreThenOneArmyAtCastleStartPosition = false;
        foreach ($fields->getField($x, $y)->getArmies() as $fieldArmyId => $fieldArmyColor) {
            if ($fieldArmyColor == $color) {
                if ($moreThenOneArmyAtCastleStartPosition) {
                    $path = new Cli_Model_Path(array(0 => array(
                        'x' => $x,
                        'y' => $y,
                        't' => 'c')
                    ), $army, $game->getTerrain());
                    $army->move($game, $path, $handler);
                    break;
                }
//                echo 'GGG ';
                $army = $armies->getArmy($fieldArmyId);
                $army->resetOldPath();
                $armyId = $army->getId();
                $moreThenOneArmyAtCastleStartPosition = true;
            }
        }


        $countGarrisonUnits = $army->getWalkingSoldiers()->count();
        $heroes = $army->getHeroes();
        $walk = $army->getWalkingSoldiers();
        $swim = $army->getSwimmingSoldiers();
        $fly = $army->getFlyingSoldiers();
        $numberOfGarrisonUnits = $game->getNumberOfGarrisonUnits();
        $numberOfComputerArmyUnits = $game->getNumberOfComputerArmyUnits();

        if ($heroes->exists()) {
            if ($walk->exists() && $heroes->getMovesLeft() > 2 && $countGarrisonUnits > $numberOfGarrisonUnits + $numberOfComputerArmyUnits) {
                $this->_newArmyId = $armies->create($army->getX(), $army->getY(), $army->getColor(), $game, $db);
                foreach ($heroes->getKeys() as $heroId) {
                    $armies->changeHeroAffiliation($armyId, $this->_newArmyId, $heroId, $gameId, $db);
                }
            } else {
                foreach ($heroes->getKeys() as $heroId) {
                    $heroes->getHero($heroId)->zeroMovesLeft($gameId, $db);
                }
            }
        } elseif ($swim->exists()) {
            if ($walk->exists() && $swim->getMovesLeft() > 2 && $countGarrisonUnits > $numberOfGarrisonUnits + $numberOfComputerArmyUnits) {
                if (empty($this->_newArmyId)) {
                    $this->_newArmyId = $armies->create($army->getX(), $army->getY(), $army->getColor(), $game, $db);
                }
                foreach ($swim->getKeys() as $soldierId) {
                    $armies->changeSwimmingSoldierAffiliation($armyId, $this->_newArmyId, $soldierId, $gameId, $db);
                }
            } else {
                foreach ($swim->getKeys() as $soldierId) {
                    $swim->getSoldier($soldierId)->zeroMovesLeft($gameId, $db);
                }
            }
        } elseif ($fly->exists()) {
            if ($walk->exists() && $fly->getMovesLeft() > 2 && $countGarrisonUnits > $numberOfGarrisonUnits + $numberOfComputerArmyUnits) {
                if (empty($this->_newArmyId)) {
                    $this->_newArmyId = $armies->create($army->getX(), $army->getY(), $army->getColor(), $game, $db);
                }
                foreach ($fly->getKeys() as $soldierId) {
                    $armies->changeFlyingSoldierAffiliation($armyId, $this->_newArmyId, $soldierId, $gameId, $db);
                }
            } else {
                foreach ($fly->getKeys() as $soldierId) {
                    $fly->getSoldier($soldierId)->zeroMovesLeft($gameId, $db);
                }
            }
        }

//        echo '$countGarrisonUnits=' . $countGarrisonUnits . ' > $numberOfUnits=' . $numberOfUnits . "\n";
        // znajduję nadmiarowe jednostki
        if ($countGarrisonUnits > $numberOfGarrisonUnits + $numberOfComputerArmyUnits) {
//            echo 'kkk ';
            $count = 0;
            if (empty($this->_newArmyId)) {
//                echo 'lll ';
                $this->_newArmyId = $armies->create($army->getX(), $army->getY(), $army->getColor(), $game, $db);
            }

            foreach ($walk->getKeys() as $soldierId) {
//                echo 'mmm ';
                $count++;
                if ($count > $numberOfGarrisonUnits) {
//                    echo 'nnn ';
                    $armies->changeWalkingSoldierAffiliation($armyId, $this->_newArmyId, $soldierId, $gameId, $db);
                }
            }
        }

        $armies->getArmy($armyId)->setFortified(true);

        if ($this->_newArmyId) {
//            echo 'ooo ';
            $token = array(
                'type' => 'split',
                'parentArmy' => $armies->getArmy($armyId)->toArray(),
                'childArmy' => $armies->getArmy($this->_newArmyId)->toArray(),
                'color' => $army->getColor()
            );
            $handler->sendToChannel($token);
        }
    }

    public function getNewArmyId()
    {
        return $this->_newArmyId;
    }
}