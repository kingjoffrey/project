<?php

class Cli_Model_Fortify
{

    function __construct($armyId, $fortify, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        if (empty($armyId)) {
            $l = new Coret_Model_Logger('Cli_Model_Fortify');
            $l->log('No "armyId"!');
            return;
        }

        $game = Cli_CommonHandler::getGameFromUser($user);
        $armies = $game->getPlayers()->getPlayer($user->parameters['me']->getColor())->getArmies();
        if ($armies->hasArmy($armyId)) {
            $armies->getArmy($armyId)->setFortified($fortify, $game->getId(), $handler->getDb());
        }
    }

}