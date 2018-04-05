<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_JoinArmy
{

    public function __construct($armyId, WebSocketTransportInterface $user, $handler)
    {
        if (empty($armyId)) {
            $l = new Coret_Model_Logger('Cli_Model_JoinArmy');
            $l->log('Brak "armyId"!');
            $handler->sendError($user, 'Error 1013');
            return;
        }

        $color = $user->parameters['me']->getColor();
        $game = Cli_CommonHandler::getGameFromUser($user);
        $armies = $game->getPlayers()->getPlayer($color)->getArmies();
        $db = $handler->getDb();
        $joinIds = $armies->joinAtPosition($armyId, $game, $db);

        $token = array(
            'type' => 'join',
            'army' => $armies->getArmy($armyId)->toArray(),
            'deletedIds' => $joinIds,
            'color' => $color
        );

        $handler->sendToChannel($token);
    }
}