<?php

class Cli_Model_Fortify
{

    function  __construct($armyId, $fortify, Cli_Model_Me $me, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if (empty($armyId)) {
            $gameHandler->sendError($user, 'No "armyId"!');
            return;
        }

        $game->getPlayers()->getPlayer($me->getColor())->getArmies()->getArmy($armyId)->setFortified($fortify, $game->getId(), $db);
    }

}