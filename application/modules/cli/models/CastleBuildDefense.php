<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_CastleBuildDefense
{
    private $_price = 100;

    /**
     * Cli_Model_CastleBuildDefense constructor.
     * @param $playerId
     * @param $castleId
     * @param WebSocketTransportInterface $user
     * @param $handler
     */
    public function __construct($playerId, $castleId, WebSocketTransportInterface $user, $handler)
    {
        if ($castleId == null) {
            $l = new Coret_Model_Logger('Cli_Model_CastleBuildDefense');
            $l->log('Brak "castleId"!');
            return;
        }

        $game = Cli_CommonHandler::getGameFromUser($user);
        $color = $game->getPlayerColor($playerId);
        $player = $game->getPlayers()->getPlayer($color);
        $castle = $player->getCastles()->getCastle($castleId);

        if (!$castle) {
            $l = new Coret_Model_Logger('Cli_Model_CastleBuildDefense');
            $l->log('To nie jest Twój zamek.');
            if (!$player->getComputer()) {
                $handler->sendError($user, 'Error 1001');
            }
            return;
        }

        $costs = 0;
        for ($i = 1; $i <= $castle->getDefense(); $i++) {
            $costs += $i * $this->_price;
        }

        if ($player->getGold() < $costs) {
            $l = new Coret_Model_Logger('Cli_Model_CastleBuildDefense');
            $l->log('Za mało złota!');
            if (!$player->getComputer()) {
                $handler->sendError($user, 'Error 1002');
            }
            return;
        }

        $gameId = $game->getId();
        $db = $handler->getDb();
        $castle->increaseDefenceMod($playerId, $gameId, $db);
        $player->addGold(-$costs);
        $player->saveGold($gameId, $db);

        $token = array(
            'type' => 'defense',
            'color' => $color,
            'gold' => $costs,
            'defense' => $castle->getDefense(),
            'castleId' => $castleId
        );

        $handler->sendToChannel($token);
    }
}