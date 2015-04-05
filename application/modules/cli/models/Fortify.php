<?php

class Cli_Model_Fortify
{

    function  __construct($armyId, $fortify,  Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if (empty($armyId)) {
            $gameHandler->sendError($user, 'No "armyId"!');
            return;
        }

        $game = Cli_Model_Game::getGame($user);
        $game->getPlayers()->getPlayer($user->parameters['me']->getColor())->getArmies()->getArmy($armyId)->setFortified($fortify, $game->getId(), $db);
    }

}