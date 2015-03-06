<?php

class Cli_Model_DisbandArmy
{

    public function __construct($armyId, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if (empty($armyId)) {
            $gameHandler->sendError($user, 'Brak "armyId"!');
            return;
        }

        $color = $game->getMe()->getColor();
        if ($armies = $game->getPlayers()->getPlayer($color)->getArmies()) {
            $armies->removeArmy($armyId, $game, $db);
            $token = array(
                'type' => 'disband',
                'id' => $armyId,
                'color' => $color
            );
            $gameHandler->sendToChannel($db, $token, $game->getId());
        } else {
            $gameHandler->sendError($user, 'Nie mogę usunąć armii!');
        }
    }

}