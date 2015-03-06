<?php

class Cli_Model_Surrender
{

    public function __construct(IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $playerId = $game->getMe()->getId();
        $color = $game->getPlayerColor($playerId);
        $player = $game->getPlayers()->getPlayer($color);
        $armies = $player->getArmies();
        $castles = $player->getCastles();

        foreach ($armies->getKeys() as $armyId) {
            $armies->removeArmy($armyId, $game, $db);
        }

        foreach ($castles->getKeys() as $castleId) {
            $castles->razeCastle($castleId, $playerId, $game, $db);
        }

        $token = array(
            'type' => 'surrender',
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $game->getId());
    }

}