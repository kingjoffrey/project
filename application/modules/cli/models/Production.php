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

        if (isset($dataIn['relocationToCastleId'])) {
            if ($dataIn['relocationToCastleId'] == $castleId) {
                $handler->sendError($user, 'Can\'t relocate production to the same castle!');
                return;
            }
            $relocationToCastleId = $dataIn['relocationToCastleId'];
        } else {
            $relocationToCastleId = null;
        }

        if ($castleId === null) {
            $handler->sendError($user, 'No "castleId"!');
            return;
        }

        if (empty($unitId)) {
            $handler->sendError($user, 'No "unitId"!');
            return;
        }

        $color = $game->getPlayerColor($playerId);

        $castle = $game->getPlayers()->getPlayer($color)->getCastles()->getCastle($castleId);

        if (!$castle) {
            $handler->sendError($user, 'To nie jest Twój zamek!');
            return;
        }

        $relocationToCastle = $game->getPlayers()->getPlayer($color)->getCastles()->getCastle($relocationToCastleId);

        if ($relocationToCastleId && !$relocationToCastle) {
            $handler->sendError($user, 'To nie jest Twój zamek!');
            return;
        }

        if ($unitId != -1) {
            if (!$castle->canProduceThisUnit($unitId)) {
                $this->sendError($user, 'Can\'t produce this unit here!');
                return;
            }
        } else {
            $unitId = null;
        }

        if (empty($relocationToCastleId) && $castle->getProductionId() == $unitId) {
            return;
        }

        try {
            $db = $handler->getDb();
            $castle->setProductionId($unitId, $relocationToCastleId, $playerId, $gameId, $db);
        } catch (Exception $e) {
            $handler->sendError($user, 'Set castle production error!');
            $l = new Coret_Model_Logger('Production');
            $l->log($e);
            return;
        }
        $token = array(
            'type' => $dataIn['type'],
            'unitId' => $unitId,
            'castleId' => $castleId,
            'relocationToCastleId' => $relocationToCastleId
        );

        $handler->sendToUser($user, $token);
    }
}