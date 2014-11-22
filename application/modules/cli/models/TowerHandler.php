<?php

class Cli_Model_TowerHandler
{

    public function __construct($path, Cli_model_game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
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

        $gameId = $game->getId();
        $fields = $game->getFields();
        $me = $game->getMe();
        $playerId = $me->getId();
        $myColor = $me->getColor();
        $myTeam = $me->getTeam();

        foreach ($surroundings as $y => $row) {
            foreach (array_keys($row) as $x) {
                if ($towerId = $fields->isTower($x, $y)) {
                    if ($fields->isArmy($x, $y)) {
                        continue;
                    }

                    $towerColor = $fields->getTowerColor($x, $y);

                    if ($towerColor == $myColor) {
                        continue;
                    }

                    if ($myTeam == $game->getPlayerTeam($game->getPlayerId($towerColor))) {
                        continue;
                    }

                    $game->addTower($playerId, $towerId, $towerColor, $db);

                    $token = array(
                        'type' => 'tower',
                        'towerId' => $towerId,
                        'color' => $myColor
                    );
                    $gameHandler->sendToChannel($db, $token, $gameId);
                }
            }
        }
    }

}