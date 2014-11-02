<?php

class Cli_Model_TowerHandler
{

    public function __construct($path, $playerId, $gameId, $db, $gameHandler)
    {
        if (empty($path)) {
            return;
        }

        $mTowersInGame = new Application_Model_TowersInGame($gameId, $db);
        $mArmy = new Application_Model_Army($gameId, $db);
        $teams = Zend_Registry::get('teams');
        $towers = Zend_Registry::get('towers');
        $playersInGameColors = Zend_Registry::get('playersInGameColors');
        $surroundings = array();

        $color = $playersInGameColors[$playerId];

        foreach ($path as $step) {
            $surroundings[$step['y'] - 1][$step['x'] - 1] = 1;
            $surroundings[$step['y'] - 1][$step['x']] = 1;
            $surroundings[$step['y'] - 1][$step['x'] + 1] = 1;
            $surroundings[$step['y']][$step['x'] - 1] = 1;
            $surroundings[$step['y']][$step['x']] = 1;
            $surroundings[$step['y']][$step['x'] + 1] = 1;
            $surroundings[$step['y'] + 1][$step['x'] - 1] = 1;
            $surroundings[$step['y'] + 1][$step['x']] = 1;
            $surroundings[$step['y'] + 1][$step['x'] + 1] = 1;
        }


        foreach ($towers as $towerId => $tower) {
            if (isset($surroundings[$tower['y']]) && isset($surroundings[$tower['y']][$tower['x']])) {
                if ($mArmy->isAnyArmyAtPosition($tower)) {
                    continue;
                }

                if ($towerOwnerId = $mTowersInGame->getTowerOwnerId($towerId)) {
//                    if ($towerOwnerId == $playerId) {
//                        continue;
//                    }
                    if ($teams[$towerOwnerId] == $teams[$playerId]) {
                        continue;
                    }

                    $mTowersInGame->changeTowerOwner($towerId, $playerId);

                    $token = array(
                        'type' => 'tower',
                        'towerId' => $towerId,
                        'color' => $color
                    );
                    $gameHandler->sendToChannel($db, $token, $gameId);
                } else {
                    $mTowersInGame->addTower($towerId, $playerId);
                    $token = array(
                        'type' => 'tower',
                        'towerId' => $towerId,
                        'color' => $color
                    );
                    $gameHandler->sendToChannel($db, $token, $gameId);
                }
            }
        }

//        if (!$mArmy->isPlayerArmyNearTowerPosition($towers[$towerId], $playerId)) {
//            $gameHandler->sendError($user, 'No player army near tower!');
//            return;
//        }
    }

}