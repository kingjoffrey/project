<?php

class Cli_Model_JoinArmy
{

    public function __construct($armyId, Cli_Model_Me $me, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if (empty($armyId)) {
            $gameHandler->sendError($user, 'Brak "armyId"!');
            return;
        }

        $color = $me->getColor();
        $armies = $game->getPlayers()->getPlayer($color)->getArmies();
        $joinIds = $armies->joinAtPosition($armyId, $game, $db);

        $token = array(
            'type' => 'join',
            'army' => $armies->getArmy($armyId)->toArray(),
            'deletedIds' => $joinIds,
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $game->getId());
    }
}