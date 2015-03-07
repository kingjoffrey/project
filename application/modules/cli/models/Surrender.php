<?php

class Cli_Model_Surrender
{

    public function __construct(IWebSocketConnection $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $playerId = $user->parameters['me']->getId();
        $color = $user->parameters['game']->getPlayerColor($playerId);
        $player = $user->parameters['game']->getPlayers()->getPlayer($color);
        $armies = $player->getArmies();
        $castles = $player->getCastles();

        foreach ($armies->getKeys() as $armyId) {
            $armies->removeArmy($armyId, $user->parameters['game'], $db);
        }

        foreach ($castles->getKeys() as $castleId) {
            $castles->razeCastle($castleId, $playerId, $user->parameters['game'], $db);
        }

        $token = array(
            'type' => 'surrender',
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['game']->getId());
    }

}