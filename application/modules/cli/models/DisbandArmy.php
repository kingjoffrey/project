<?php

class Cli_Model_DisbandArmy
{

    public function __construct($armyId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_GameHandler $handler)
    {
        if (empty($armyId)) {
            $handler->sendError($user, 'Brak "armyId"!');
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
            $handler->sendToChannel($game, $token);
        } else {
            $handler->sendError($user, 'Nie mogę usunąć armii!');
        }
    }

}