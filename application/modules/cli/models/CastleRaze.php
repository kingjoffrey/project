<?php

class Cli_Model_CastleRaze
{

    public function __construct($armyId, IWebSocketConnection $user, Cli_Model_Me $me, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if ($armyId == null) {
            $gameHandler->sendError($user, 'No "armyId"!');
            return;
        }

        $playerId = $me->getId();
        $color = $user->parameters['game']->getPlayerColor($playerId);
        $player = $user->parameters['game']->getPlayers()->getPlayer($color);
        $army = $player->getArmies()->getArmy($armyId);
        $field = $user->parameters['game']->getFields()->getField($army->getX(), $army->getY());
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

        $castles->razeCastle($castleId, $playerId, $user->parameters['game'], $db);
        $player->addGold($defense * 200);

        $token = array(
            'type' => 'raze',
            'color' => $color,
            'gold' => $player->getGold(),
            'castleId' => $castleId
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['game']->getId());
    }

}