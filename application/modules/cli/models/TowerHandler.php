<?php

class Cli_Model_TowerHandler
{

    public function __construct($path, $user, $db, $gameHandler)
    {
        if (empty($path)) {
            return;
        }

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
                        $user->parameters['game']->addTower($user->parameters['playerIdId'], $towerId, $type, $db);

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
    }

}