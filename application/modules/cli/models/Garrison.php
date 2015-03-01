<?php

class Cli_Model_Garrison
{
    private $_newArmyId = 0;

    public function __construct($numberOfUnits, $x, $y, Cli_Model_Armies $armies, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        $gameId = $game->getId();

        foreach ($armies->getKeys() as $armyId) {
            $army = $armies->getArmy($armyId);
            for ($i = $x; $i <= $x + 1; $i++) {
                for ($j = $y; $j <= $y + 1; $j++) {
                    if ($army->getX() == $i && $army->getY() == $j) {
                        if ($army->getX() != $x && $army->getY() != $y) {
                            $path = new Cli_Model_Path(array(0 => array(
                                'x' => $x,
                                'y' => $y,
                                'tt' => 'c')
                            ), $army);
                            $army->move($game, $path, $db, $gameHandler);
                        }
                    }
                }
            }
        }

        if (!isset($army)) {
            return;
        }

        $army->setFortified(true, $gameId, $db);
        $countGarrisonUnits = $army->getSoldiers()->count();
        $heroes = $army->getHeroes();
        $ships = $army->getShips();

        if ($heroes->exists() || $ships->exists()) {
            echo 'a00' . "\n";
            $this->_newArmyId = $armies->create($army->getX(), $army->getY(), $army->getColor(), $game, $db);
            foreach ($heroes->getKeys() as $heroId) {
                echo 'a01' . "\n";
                $armies->changeHeroAffiliation($armyId, $this->_newArmyId, $heroId, $gameId, $db);
            }
            foreach ($ships->getKeys() as $soldierId) {
                echo 'a02' . "\n";
                $armies->changeShipAffiliation($armyId, $this->_newArmyId, $soldierId, $gameId, $db);
            }
        }

        // znajduję nadmiarowe jednostki
        if ($countGarrisonUnits > $numberOfUnits) {
            echo 'a11' . "\n";
            $count = 0;
            $armySoldiers = $army->getSoldiers();
            if (empty($this->_newArmyId)) {
                echo 'a12' . "\n";
                $this->_newArmyId = $armies->create($army->getX(), $army->getY(), $army->getColor(), $game, $db);
            }

            foreach ($armySoldiers->getKeys() as $soldierId) {
                $count++;
                echo 'a' . $count . "\n";
                if ($count > $numberOfUnits) {
                    $armies->changeSoldierAffiliation($armyId, $this->_newArmyId, $soldierId, $gameId, $db);
                }
            }
        }

        if ($this->_newArmyId) {
            echo 'b000' . "\n";
            $token = array(
                'type' => 'split',
                'parentArmy' => $army->toArray(),
                'childArmy' => $armies->getArmy($this->_newArmyId),
                'color' => $army->getColor()
            );
            $gameHandler->sendToChannel($db, $token, $gameId);
        }
    }

    public function getNewArmyId()
    {
        return $this->_newArmyId;
    }
}