<?php

class Cli_Model_Fortify
{

    function  __construct($armyId, $fortify, $user, $db, $gameHandler)
    {
        if (empty($armyId)) {
            $gameHandler->sendError($user, 'No "armyId"!');
            return;
        }

        $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
        $mArmy2->fortify($armyId, $fortify, $user->parameters['playerId']);
    }

}