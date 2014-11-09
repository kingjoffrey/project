<?php

class Cli_Model_Production
{
    public function __construct($dataIn, $user, $db, $gameHandler)
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

        if (!$user->parameters['game']->isPlayerCastle($user->parameters['playerId'], $castleId)) {
            $gameHandler->sendError($user, 'To nie jest Twój zamek!');
            return;
        }

        if ($relocationToCastleId && !$user->parameters['game']->isPlayerCastle($user->parameters['playerId'], $relocationToCastleId)) {
            $gameHandler->sendError($user, 'To nie jest Twój zamek!');
            return;
        }

        if ($unitId != -1) {
            if (!$user->parameters['game']->canCastleProduceThisUnit($user->parameters['playerId'], $castleId, $unitId)) {
                $this->sendError($user, 'Can\'t produce this unit here!');
                return;
            }
        } else {
            $unitId = null;
        }

        if (empty($relocationToCastleId) && $user->parameters['game']->getCastleCurrentProductionId($user->parameters['playerId'], $castleId) == $unitId) {
            return;
        }

        try {
            $user->parameters['game']->setProductionId($user->parameters['playerId'], $castleId, $unitId, $relocationToCastleId, $db);
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