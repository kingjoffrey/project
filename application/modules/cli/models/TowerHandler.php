<?php

class Cli_Model_TowerHandler
{
    public function __construct($playerId, Cli_Model_Path $path, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
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
                    if ($fields->hasField($x, $y) && $towerId = $fields->getField($x, $y)->getTowerId()) {
                        if ($fields->getField($x, $y)->isArmy() && !$player->getArmies()->getArmyIdFromField($fields->getField($x, $y))) {
                            continue;
                        }

                        $towerColor = $fields->getField($x, $y)->getTowerColor();

                        if ($towerColor == $playerColor) {
                            continue;
                        }

                        $oldOwner = $players->getPlayer($towerColor);
                        if ($playerTeam == $oldOwner->getTeam()) {
                            continue;
                        }

                        $player->addTower($towerId, $oldOwner->getTowers()->getTower($towerId), $towerColor, $fields, $gameId, $db);
                        $oldOwner->getTowers()->removeTower($towerId);

                        $token = array(
                            'type' => 'tower',
                            'x' => $x,
                            'y' => $y,
                            'color' => $playerColor
                        );
                        $gameHandler->sendToChannel($game, $token);
                    }
                }
            }
        }
    }
}