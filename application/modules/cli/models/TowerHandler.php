<?php

class Cli_Model_TowerHandler
{
    public function __construct($playerId, Cli_Model_Path $path, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        $current = $path->getCurrent();

        if (empty($current)) {
            return;
        }

        $gameId = $game->getId();
        $fields = $game->getFields();
        $players = $game->getPlayers();
        $playerColor = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($playerColor);
        $playerTeam = $player->getTeam();

        foreach ($current as $step) {
            for ($y = $step['y'] - 1; $y <= $step['y'] + 1; $y++) {
                for ($x = $step['x'] - 1; $x <= $step['x'] + 1; $x++) {
                    if ($towerId = $fields->isTower($x, $y)) {
                        if ($fields->getField($x, $y)->isArmy()) {
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

                        $player->addTower($towerId, $oldOwner->getTowers()->getTower($towerId), $towerColor, $fields, $gameId, $db);
                        $oldOwner->removeTower($towerId);

                        $token = array(
                            'type' => 'tower',
                            'x' => $x,
                            'y' => $y,
                            'color' => $playerColor
                        );
                        $gameHandler->sendToChannel($db, $token, $gameId);
                    }
                }
            }
        }
    }
}