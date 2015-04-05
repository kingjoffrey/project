<?php

class Cli_Model_Surrender
{

    public function __construct(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $playerId = $user->parameters['me']->getId();
        $game = Cli_Model_Game::getGame($user);
        $color = $game->getPlayerColor($playerId);
        $player = $game->getPlayers()->getPlayer($color);
        $armies = $player->getArmies();
        $castles = $player->getCastles();

        foreach ($armies->getKeys() as $armyId) {
            $armies->removeArmy($armyId, $game, $db);
        }

        foreach ($castles->getKeys() as $castleId) {
            $castles->razeCastle($castleId, $playerId, $game, $db);
        }

        $token = array(
            'type' => 'surrender',
            'color' => $color
        );

        $gameHandler->sendToChannel($game, $token);
    }

}