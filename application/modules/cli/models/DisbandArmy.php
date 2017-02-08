<?php

class Cli_Model_DisbandArmy
{

    public function __construct($armyId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        if (empty($armyId)) {
            $l = new Coret_Model_Logger('Cli_Model_DisbandArmy');
            $l->log('Brak "armyId"!');
            $handler->sendError($user, 'Error 1006');
            return;
        }

        $color = $user->parameters['me']->getColor();
        $game = Cli_CommonHandler::getGameFromUser($user);
        if (!$armies = $game->getPlayers()->getPlayer($color)->getArmies()) {
            $l = new Coret_Model_Logger('Cli_Model_DisbandArmy');
            $l->log('Nie mogę usunąć armii!');
            $handler->sendError($user, 'Error 1007');
        }

        $db = $handler->getDb();
        $armies->removeArmy($armyId, $game, $db);
        $token = array(
            'type' => 'disband',
            'id' => $armyId,
            'color' => $color
        );
        $handler->sendToChannel($token);

    }

}