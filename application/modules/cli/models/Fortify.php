<?php

class Cli_Model_Fortify
{

    function  __construct($armyId, $fortify,  IWebSocketConnection $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if (empty($armyId)) {
            $gameHandler->sendError($user, 'No "armyId"!');
            return;
        }

        $user->parameters['game']->getPlayers()->getPlayer($user->parameters['me']->getColor())->getArmies()->getArmy($armyId)->setFortified($fortify, $user->parameters['game']->getId(), $db);
    }

}