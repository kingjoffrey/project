<?php

class Cli_Model_CastleBuildDefense
{
    public function __construct($castleId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_GameHandler $handler)
    {
        if ($castleId == null) {
            $handler->sendError($user, 'Brak "castleId"!');
            return;
        }


        $playerId = $user->parameters['me']->getId();
        $game = Cli_Model_Game::getGame($user);
        $color = $game->getPlayerColor($playerId);
        $player = $game->getPlayers()->getPlayer($color);
        $castle = $player->getCastles()->getCastle($castleId);

        if (!$castle) {
            $handler->sendError($user, 'To nie jest Twój zamek.');
            return;
        }

        $costs = 0;
        for ($i = 1; $i <= $castle->getDefenseModifier(); $i++) {
            $costs += $i * 100;
        }
        if ($player->getGold() < $costs) {
            $handler->sendError($user, 'Za mało złota!');
            return;
        }

        $gameId = $game->getId();
        $castle->increaseDefenceMod($playerId, $gameId, $db);
        $player->addGold(-$costs);

        $token = array(
            'type' => 'defense',
            'color' => $color,
            'gold' => $player->getGold(),
            'defense' => $castle->getDefenseModifier(),
            'castleId' => $castleId
        );

        $handler->sendToChannel($game, $token);
    }
}