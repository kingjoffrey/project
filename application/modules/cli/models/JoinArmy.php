<?php

class Cli_Model_JoinArmy
{

    public function __construct($armyId, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        if (empty($armyId)) {
            $gameHandler->sendError($user, 'Brak "armyId"!');
            return;
        }

        $gameId = $game->getId();
        $color = $game->getMe()->getColor();
        $armies = $game->getPlayers()->getPlayer($color)->getArmies();
        $joinIds = $armies->joinAtPosition($armyId, $gameId, $db);

        $token = array(
            'type' => 'join',
            'army' => $armies->getArmy($armyId)->toArray(),
            'deletedIds' => $joinIds,
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $gameId);
    }
}