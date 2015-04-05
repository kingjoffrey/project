<?php

class Cli_Model_DisbandArmy
{

    public function __construct($armyId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if (empty($armyId)) {
            $gameHandler->sendError($user, 'Brak "armyId"!');
            return;
        }

        $color = $user->parameters['me']->getColor();
        $game = Cli_Model_Game::getGame($user);
        if ($armies = $game->getPlayers()->getPlayer($color)->getArmies()) {
            $armies->removeArmy($armyId, $game, $db);
            $token = array(
                'type' => 'disband',
                'id' => $armyId,
                'color' => $color
            );
            $gameHandler->sendToChannel($game, $token);
        } else {
            $gameHandler->sendError($user, 'Nie mogę usunąć armii!');
        }
    }

}