<?php

class Cli_Model_Surrender
{

    public function __construct(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        $playerId = $user->parameters['me']->getId();
        $game = Cli_CommonHandler::getGameFromUser($user);
        $color = $game->getPlayerColor($playerId);
        $player = $game->getPlayers()->getPlayer($color);
        $armies = $player->getArmies();
        $castles = $player->getCastles();
        $db = $handler->getDb();

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

        $handler->sendToChannel($game, $token);
    }

}