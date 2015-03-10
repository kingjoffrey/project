<?php

class Cli_Model_CastleBuildDefense
{
    public function __construct($castleId, IWebSocketConnection $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if ($castleId == null) {
            $gameHandler->sendError($user, 'Brak "castleId"!');
            return;
        }


        $playerId = $user->parameters['me']->getId();
        $game = $this->getGame($user);
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

    /**
     * @param IWebSocketConnection $user
     * @return Cli_Model_Game
     */
    private function getGame(IWebSocketConnection $user)
    {
        return $user->parameters['game'];
    }
}