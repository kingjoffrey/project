<?php

class Cli_Model_StartTurn
{

    public function __construct($playerId, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $players = $game->getPlayers();
        $gameId = $game->getId();
        $player = $players->getPlayer($game->getPlayerColor($playerId));
        $game->activatePlayerTurn($playerId, $db);

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
            'color' => $game->getPlayerColor($playerId)
        );
        $gameHandler->sendToChannel($db, $token, $gameId);
    }

}
