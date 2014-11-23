<?php

class Cli_Model_TowerHandler
{

    public function __construct($playerId, $path, Cli_model_game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
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
        $playerColor = $game->getPlayerColor($playerId);
        $playerTeam = $game->getPlayerTeam($playerId);

        foreach ($surroundings as $y => $row) {
            foreach (array_keys($row) as $x) {
                if ($towerId = $fields->isTower($x, $y)) {
                    if ($fields->isArmy($x, $y)) {
                        continue;
                    }

                    $towerColor = $fields->getTowerColor($x, $y);

                    if ($towerColor == $playerColor) {
                        continue;
                    }

                    if ($playerTeam == $game->getPlayerTeam($game->getPlayerId($towerColor))) {
                        continue;
                    }

                    $game->changeTowerOwner($playerId, $towerId, $towerColor, $db);

                    $token = array(
                        'type' => 'tower',
                        'towerId' => $towerId,
                        'color' => $playerColor
                    );
                    $gameHandler->sendToChannel($db, $token, $gameId);
                }
            }
        }
    }

}