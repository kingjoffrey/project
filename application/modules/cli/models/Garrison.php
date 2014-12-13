<?php

class Cli_Model_Garrison
{
    private $_newArmy;

    public function __construct($numberOfUnits, $x, $y, Cli_Model_Armies $armies, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        $garrison = new Cli_Model_Armies();
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
                        $countGarrisonUnits = $army->getSoldiers()->count();
                    }
                }
            }
        }

        if (!isset($army)) {
            return;
        }

        $army->setFortified(true, $gameId, $db);
        $garrison->addArmy($armyId, $army);

        $heroes = $army->getHeroes();
        $ships = $army->getShips();
        $newArmyId = 0;

        if ($heroes->exists() || $ships->exists()) {
            $newArmyId = $garrison->create($army->getX(), $army->getY(), $army->getColor(), $game, $db);
            $army = $garrison->getArmy($newArmyId);
            foreach ($heroes->getKeys() as $heroId) {
                $garrison->moveHero($armyId, $newArmyId, $heroId, $gameId, $db);
            }
            foreach ($ships->getKeys() as $soldierId) {
                $garrison->moveShip($armyId, $newArmyId, $soldierId, $gameId, $db);
            }
        }

        // znajduję nadmiarowe jednostki
        if ($countGarrisonUnits > $numberOfUnits) {
            $count = 0;
            $armySoldiers = $army->getSoldiers();
            if (empty($newArmyId)) {
                $newArmyId = $garrison->create($army->getX(), $army->getY(), $army->getColor(), $game, $db);
            }

            foreach ($armySoldiers->getKeys() as $soldierId) {
                $count++;
                if ($count > $numberOfUnits) {
                    $garrison->moveSoldier($armyId, $newArmyId, $soldierId, $gameId, $db);
                }
            }
        }

        if ($newArmyId) {
            $this->_newArmy = $garrison->getArmy($newArmyId);
            $token = array(
                'type' => 'split',
                'parentArmy' => $army->toArray(),
                'childArmy' => $this->_newArmy->toArray(),
                'color' => $army->getColor()
            );
            $gameHandler->sendToChannel($db, $token, $gameId);
        }
    }

    public function getArmyToGo()
    {
        return $this->_newArmy;
    }
}