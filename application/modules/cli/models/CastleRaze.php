<?php

class Cli_Model_CastleRaze
{
    /**
     * Cli_Model_CastleRaze constructor.
     * @param $armyId
     * @param \Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param $handler
     */
    public function __construct($armyId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        if ($armyId == null) {
            $handler->sendError($user, 'No "armyId"!');
            return;
        }

        $game = Cli_Model_Game::getGame($user);
        $playerId = $user->parameters['me']->getId();
        $color = $game->getPlayerColor($playerId);
        $player = $game->getPlayers()->getPlayer($color);
        $army = $player->getArmies()->getArmy($armyId);
        $field = $game->getFields()->getField($army->getX(), $army->getY());
        $castleId = $field->getCastleId();
        if (!$castleId) {
            $handler->sendError($user, 'Brak zamku!');
            return;
        }
        if ($field->getCastleColor() != $color) {
            $handler->sendError($user, 'To nie jest twój zamek!');
            return;
        }

        $castles = $player->getCastles();
        $defense = $castles->getCastle($castleId)->getDefenseModifier();

        $db = $handler->getDb();
        $castles->razeCastle($castleId, $playerId, $game, $db);
        $player->addGold($defense * 200);

        $token = array(
            'type' => 'raze',
            'color' => $color,
            'gold' => $player->getGold(),
            'castleId' => $castleId
        );

        $handler->sendToChannel($game, $token);
    }

}