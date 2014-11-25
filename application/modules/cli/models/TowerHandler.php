<?php

class Cli_Model_TowerHandler
{

    public function __construct($playerId, Cli_Model_Path $path, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
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
        $players = $game->getPlayers();
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

                    $oldOwner = $players->getPlayer($towerColor);
                    $tower = $oldOwner->getTower($towerId);
                    $oldOwner->removeTower($towerId);

                    $mTowersInGame = new Application_Model_TowersInGame($this->_id, $db);
                    if ($towerColor == 'neutral') {
                        $mTowersInGame->addTower($towerId, $playerId);
                    } else {
                        $mTowersInGame->changeTowerOwner($towerId, $playerId);
                    }

                    $game->getFields()->changeTower($tower->getX(), $tower->getY(), $playerColor);
                    $players->getPlayer($playerColor)->addTower($towerId, $tower);

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