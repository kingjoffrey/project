<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_CastleRaze
{
    /**
     * Cli_Model_CastleRaze constructor.
     * @param $armyId
     * @param WebSocketTransportInterface $user
     * @param $handler
     */
    public function __construct($armyId, WebSocketTransportInterface $user, $handler)
    {
        if ($armyId == null) {
            $l = new Coret_Model_Logger('Cli_Model_CastleRaze');
            $l->log('No "armyId"!');
            $handler->sendError($user, 'Error 1003');
            return;
        }

        $game = Cli_CommonHandler::getGameFromUser($user);
        $playerId = $user->parameters['me']->getId();
        $color = $game->getPlayerColor($playerId);
        $player = $game->getPlayers()->getPlayer($color);
        $army = $player->getArmies()->getArmy($armyId);
        $field = $game->getFields()->getField($army->getX(), $army->getY());
        $castleId = $field->getCastleId();

        if (!$castleId) {
            $l = new Coret_Model_Logger('Cli_Model_CastleRaze');
            $l->log('Brak zamku!');
            $handler->sendError($user, 'Error 1004');
            return;
        }

        if ($field->getCastleColor() != $color) {
            $l = new Coret_Model_Logger('Cli_Model_CastleRaze');
            $l->log('To nie jest twój zamek!');
            $handler->sendError($user, 'Error 1005');
            return;
        }

        $castles = $player->getCastles();
        $castle = $castles->getCastle($castleId);
        $gold = $castle->getDefense() * 200;

        $player->addGold($gold);

        $game->getFields()->razeCastle($castle->getX(), $castle->getY());

        $db = $handler->getDb();

        $castles->razeCastle($castleId, $playerId, $game, $db);

        $player->saveGold($game->getId(), $db);

        $token = array(
            'type' => 'raze',
            'color' => $color,
            'gold' => $gold,
            'castleId' => $castleId
        );

        $handler->sendToChannel($token);
    }

}