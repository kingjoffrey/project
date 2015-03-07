<?php

class Cli_Model_JoinArmy
{

    public function __construct($armyId, IWebSocketConnection $user, Cli_Model_Me $me, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if (empty($armyId)) {
            $gameHandler->sendError($user, 'Brak "armyId"!');
            return;
        }

        $color = $me->getColor();
        $armies = $user->parameters['game']->getPlayers()->getPlayer($color)->getArmies();
        $joinIds = $armies->joinAtPosition($armyId, $user->parameters['game'], $db);

        $token = array(
            'type' => 'join',
            'army' => $armies->getArmy($armyId)->toArray(),
            'deletedIds' => $joinIds,
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['game']->getId());
    }
}