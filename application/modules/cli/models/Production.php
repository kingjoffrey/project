<?php

class Cli_Model_Production
{
    public function __construct($dataIn, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $castleId = $dataIn['castleId'];
        $unitId = $dataIn['unitId'];
        if (isset($dataIn['relocationToCastleId'])) {
            if ($dataIn['relocationToCastleId'] == $castleId) {
                $gameHandler->sendError($user, 'Can\'t relocate production to the same castle!');
                return;
            }
            $relocationToCastleId = $dataIn['relocationToCastleId'];
        } else {
            $relocationToCastleId = null;
        }

        if ($castleId === null) {
            $gameHandler->sendError($user, 'No "castleId"!');
            return;
        }

        if (empty($unitId)) {
            $gameHandler->sendError($user, 'No "unitId"!');
            return;
        }

        $color=$game->getPlayerColor($user->parameters['playerId']);

        $castle = $game->getPlayers()->getPlayer($color)->getCastles()->getCastle($castleId);

        if (!$castle) {
            $gameHandler->sendError($user, 'To nie jest Twój zamek!');
            return;
        }

        $relocationToCastle = $game->getPlayers()->getPlayer($color)->getCastles()->getCastle($relocationToCastleId);

        if ($relocationToCastleId && !$relocationToCastle) {
            $gameHandler->sendError($user, 'To nie jest Twój zamek!');
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
            $castle->setProductionId($user->parameters['gameId'], $user->parameters['playerId'], $unitId, $relocationToCastleId, $db);
        } catch (Exception $e) {
            $gameHandler->sendError($user, 'Set castle production error!');
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

        $gameHandler->sendToUser($user, $db, $token, $user->parameters['gameId']);
    }
}