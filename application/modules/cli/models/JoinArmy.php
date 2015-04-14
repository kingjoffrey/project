<?php

class Cli_Model_JoinArmy
{

    public function __construct($armyId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_GameHandler $handler)
    {
        if (empty($armyId)) {
            $handler->sendError($user, 'Brak "armyId"!');
            return;
        }

        $color = $user->parameters['me']->getColor();
        $game = Cli_Model_Game::getGame($user);
        $armies = $game->getPlayers()->getPlayer($color)->getArmies();
        $joinIds = $armies->joinAtPosition($armyId, $game, $db);

        $token = array(
            'type' => 'join',
            'army' => $armies->getArmy($armyId)->toArray(),
            'deletedIds' => $joinIds,
            'color' => $color
        );

        $handler->sendToChannel($game, $token);
    }
}