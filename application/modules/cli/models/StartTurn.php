<?php

class Cli_Model_StartTurn
{

    public function __construct($playerId, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $players = $game->getPlayers();
        $gameId = $game->getId();
        $color = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($color);
        $players->activatePlayerTurn($color, $playerId, $gameId, $db);

        if ($player->getComputer()) {
            $player->unfortifyArmies($gameId, $db);
            $type = 'computerStart';
        } else {
            $type = 'startTurn';
        }

        $player->startTurn($gameId, $game->getTurnNumber(), $db);

        $token = array(
            'type' => $type,
            'gold' => $player->getGold(),
            'armies' => $player->getArmies()->toArray(),
            'castles' => $player->getCastles()->toArray(),
            'color' => $color
        );
        $gameHandler->sendToChannel($db, $token, $gameId);
    }

}
