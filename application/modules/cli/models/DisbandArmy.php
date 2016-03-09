<?php

class Cli_Model_DisbandArmy
{

    public function __construct($armyId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        if (empty($armyId)) {
            $handler->sendError($user, 'Brak "armyId"!');
            return;
        }

        $color = $user->parameters['me']->getColor();
        $game = Cli_CommonHandler::getGameFromUser($user);
        if (!$armies = $game->getPlayers()->getPlayer($color)->getArmies()) {
            $handler->sendError($user, 'Nie mogę usunąć armii!');
        }

        $db = $handler->getDb();
        $armies->removeArmy($armyId, $game, $db);
        $token = array(
            'type' => 'disband',
            'id' => $armyId,
            'color' => $color
        );
        $handler->sendToChannel($game, $token);

    }

}