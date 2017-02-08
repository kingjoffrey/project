<?php

class Cli_Model_CastleBuildDefense
{
    /**
     * Cli_Model_CastleBuildDefense constructor.
     * @param $castleId
     * @param \Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param $handler
     */
    public function __construct($castleId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        if ($castleId == null) {
            $l = new Coret_Model_Logger('Cli_Model_CastleBuildDefense');
            $l->log('Brak "castleId"!');
            return;
        }


        $playerId = $user->parameters['me']->getId();
        $game = Cli_CommonHandler::getGameFromUser($user);
        $color = $game->getPlayerColor($playerId);
        $player = $game->getPlayers()->getPlayer($color);
        $castle = $player->getCastles()->getCastle($castleId);

        if (!$castle) {
            $l = new Coret_Model_Logger('Cli_Model_CastleBuildDefense');
            $l->log('To nie jest Twój zamek.');
            $handler->sendError($user, 'Error 1001');
            return;
        }

        $costs = 0;
        for ($i = 1; $i <= $castle->getDefense(); $i++) {
            $costs += $i * 100;
        }
        if ($player->getGold() < $costs) {
            $l = new Coret_Model_Logger('Cli_Model_CastleBuildDefense');
            $l->log('Za mało złota!');
            $handler->sendError($user, 'Error 1002');
            return;
        }

        $gameId = $game->getId();
        $db = $handler->getDb();
        $castle->increaseDefenceMod($playerId, $gameId, $db);
        $player->addGold(-$costs);

        $token = array(
            'type' => 'defense',
            'color' => $color,
            'gold' => $player->getGold(),
            'defense' => $castle->getDefense(),
            'castleId' => $castleId
        );

        $handler->sendToChannel($token);
    }
}