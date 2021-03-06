<?php

class Cli_Model_Production
{
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        $castleId = $dataIn['castleId'];
        $unitId = $dataIn['unitId'];
        $game = Cli_CommonHandler::getGameFromUser($user);
        $gameId = $game->getId();
        $playerId = $user->parameters['me']->getId();

        if ($castleId === null) {
            $l = new Coret_Model_Logger('Cli_Model_Production');
            $l->log('No "castleId"!');
            $handler->sendError($user, 'Error 1023');
            return;
        }

        if (empty($unitId)) {
            $l = new Coret_Model_Logger('Cli_Model_Production');
            $l->log('No "unitId"!');
            $handler->sendError($user, 'Error 1024');
            return;
        }

        $color = $game->getPlayerColor($playerId);

        $castle = $game->getPlayers()->getPlayer($color)->getCastles()->getCastle($castleId);

        if (!$castle) {
            $l = new Coret_Model_Logger('Cli_Model_Production');
            $l->log('To nie jest Twój zamek!');
            $handler->sendError($user, 'Error 1025');
            return;
        }

        if ($unitId != -1) {
            if (!$castle->canProduceThisUnit($unitId)) {
                $l = new Coret_Model_Logger('Cli_Model_Production');
                $l->log('Can not produce this unit here!');
                $handler->sendError($user, 'Error 1027');
                return;
            }
        } else {
            $unitId = null;
        }

        if ($castle->getProductionId() == $unitId) {
            return;
        }

        try {
            $db = $handler->getDb();
            $castle->setProductionId($unitId, $playerId, $gameId, $db);
        } catch (Exception $e) {
            $l = new Coret_Model_Logger('Cli_Model_Production');
            $l->log('Set castle production error!');
            $l->log($e);
            $handler->sendError($user, 'Error 1028');
            return;
        }
        $token = array(
            'type' => $dataIn['type'],
            'unitId' => $unitId,
            'castleId' => $castleId
        );

        $handler->sendToUser($user, $token);
    }
}