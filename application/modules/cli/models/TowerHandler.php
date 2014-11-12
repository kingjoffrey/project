<?php

class Cli_Model_TowerHandler
{

    public function __construct($path, $user, $db, $gameHandler)
    {
        if (empty($path)) {
            return;
        }

        $mTowersInGame = new Application_Model_TowersInGame($user->parameters['gameId'], $db);
        $surroundings = array();

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

        foreach ($surroundings as $y => $row) {
            foreach (array_keys($row) as $x) {
                if ($towerId = $user->parameters['game']->isTowerAtField($x, $y)) {
                    if ($user->parameters['game']->isArmyAtField($x, $y)) {
                        continue;
                    }
                    if ($type = $user->parameters['game']->isTowerAtFieldOpen($x, $y)) {
                        if ($type == 'neutral') {
                            $mTowersInGame->addTower($towerId, $user->parameters['playerId']);
                        } else {
                            $mTowersInGame->changeTowerOwner($towerId, $user->parameters['playerId']);
                        }

                        $token = array(
                            'type' => 'tower',
                            'towerId' => $towerId,
                            'color' => $user->parameters['game']->getMyColor()
                        );
                        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
                    }
                }
            }
        }

//        foreach ($towers as $towerId => $tower) {
//            if (isset($surroundings[$tower['y']]) && isset($surroundings[$tower['y']][$tower['x']])) {
//                if ($mArmy->isAnyArmyAtPosition($tower)) {
//                    continue;
//                }
//
//                if ($towerOwnerId = $mTowersInGame->getTowerOwnerId($towerId)) {
////                    if ($towerOwnerId == $playerId) {
////                        continue;
////                    }
//                    if ($teams[$towerOwnerId] == $teams[$playerId]) {
//                        continue;
//                    }
//
//                    $mTowersInGame->changeTowerOwner($towerId, $playerId);
//
//                    $token = array(
//                        'type' => 'tower',
//                        'towerId' => $towerId,
//                        'color' => $color
//                    );
//                    $gameHandler->sendToChannel($db, $token, $gameId);
//                } else {
//                    $mTowersInGame->addTower($towerId, $playerId);
//                    $token = array(
//                        'type' => 'tower',
//                        'towerId' => $towerId,
//                        'color' => $color
//                    );
//                    $gameHandler->sendToChannel($db, $token, $gameId);
//                }
//            }
//        }

//        if (!$mArmy->isPlayerArmyNearTowerPosition($towers[$towerId], $playerId)) {
//            $gameHandler->sendError($user, 'No player army near tower!');
//            return;
//        }
    }

}