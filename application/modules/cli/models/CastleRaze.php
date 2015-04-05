<?php

class Cli_Model_CastleRaze
{

    public function __construct($armyId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if ($armyId == null) {
            $gameHandler->sendError($user, 'No "armyId"!');
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
            $gameHandler->sendError($user, 'Brak zamku!');
            return;
        }
        if ($field->getCastleColor() != $color) {
            $gameHandler->sendError($user, 'To nie jest twój zamek!');
            return;
        }

        $castles = $player->getCastles();
        $defense = $castles->getCastle($castleId)->getDefenseModifier();

        $castles->razeCastle($castleId, $playerId, $game, $db);
        $player->addGold($defense * 200);

        $token = array(
            'type' => 'raze',
            'color' => $color,
            'gold' => $player->getGold(),
            'castleId' => $castleId
        );

        $gameHandler->sendToChannel($game, $token);
    }

}