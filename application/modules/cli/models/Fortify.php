<?php

class Cli_Model_Fortify
{

    function  __construct($armyId, $fortify, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_GameHandler $handler)
    {
        if (empty($armyId)) {
            $handler->sendError($user, 'No "armyId"!');
            return;
        }

        $game = Cli_Model_Game::getGame($user);
        $game->getPlayers()->getPlayer($user->parameters['me']->getColor())->getArmies()->getArmy($armyId)->setFortified($fortify, $game->getId(), $handler->getDb());
    }

}