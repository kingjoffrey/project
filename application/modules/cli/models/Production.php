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

        $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);

        if (!$mCastlesInGame->isPlayerCastle($castleId, $user->parameters['playerId'])) {
            $gameHandler->sendError($user, 'To nie jest Twój zamek!');
            return;
        }

        if ($relocationToCastleId && !$mCastlesInGame->isPlayerCastle($relocationToCastleId, $user->parameters['playerId'])) {
            $gameHandler->sendError($user, 'To nie jest Twój zamek!');
            return;
        }

        if ($unitId != -1) {
            $mMapCastlesProduction = new Application_Model_CastleProduction($db);
            $production = $mMapCastlesProduction->getCastleProduction($castleId);

            if (!isset($production[$unitId])) {
                $this->sendError($user, 'Can\'t produce this unit here!');
                return;
            }
        } else {
            $unitId = null;
        }

        $production = $mCastlesInGame->getProduction($castleId, $user->parameters['playerId']);
        if (empty($relocationToCastleId) && $production['productionId'] == $unitId) {
            return;
        }

        if ($mCastlesInGame->setProduction($user->parameters['playerId'], $castleId, $unitId, $relocationToCastleId)) {
            $token = array(
                'type' => $dataIn['type'],
                'unitId' => $unitId,
                'castleId' => $castleId,
                'relocationToCastleId' => $relocationToCastleId
            );

            $gameHandler->sendToUser($user, $db, $token, $user->parameters['gameId']);
        }

    }
}