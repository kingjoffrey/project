<?php

class Cli_Model_CastleRaze
{

    public function __construct($armyId, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        if ($armyId == null) {
            $gameHandler->sendError($user, 'No "armyId"!');
            return;
        }

        $playerId = $game->getMe()->getId();
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

        $gameHandler->sendToChannel($db, $token, $game->getId());
    }

}