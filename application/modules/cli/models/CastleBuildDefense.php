<?php

class Cli_Model_CastleBuildDefense
{

    public function __construct($castleId, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        if ($castleId == null) {
            $gameHandler->sendError($user, 'Brak "castleId"!');
            return;
        }

        $playerId = $game->getMe()->getId();
        $color = $game->getPlayerColor($playerId);
        $player = $game->getPlayers()->getPlayer($color);
        $castle = $player->getCastles()->getCastle($castleId);

        if (!$castle) {
            $gameHandler->sendError($user, 'To nie jest Twój zamek.');
            return;
        }

        $costs = 0;
        for ($i = 1; $i <= $castle->getDefenseModifier(); $i++) {
            $costs += $i * 100;
        }
        if ($player->getGold() < $costs) {
            $gameHandler->sendError($user, 'Za mało złota!');
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

        $gameHandler->sendToChannel($db, $token, $gameId);
    }

}