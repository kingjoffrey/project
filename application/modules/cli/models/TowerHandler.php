<?php

class Cli_Model_TowerHandler
{

    public function __construct($playerId, Cli_Model_Path $path, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        $current = $path->getCurrent();

        if (empty($current)) {
            return;
        }

        $surroundings = array();

        foreach ($current as $step) {
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
        $players = $game->getPlayers();
        $playerColor = $game->getPlayerColor($playerId);
        $playerTeam = $players->getPlayer($playerColor)->getTeam();

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

                    $oldOwner = $players->getPlayer($towerColor);
                    if ($playerTeam == $oldOwner->getTeam()) {
                        continue;
                    }

                    $players->getPlayer($playerColor)->addTower($towerId, $oldOwner->getTowers()->getTower($towerId), $towerColor, $fields, $gameId, $db);
                    $oldOwner->removeTower($towerId);

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