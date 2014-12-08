<?php

class Cli_Model_StartTurn
{

    public function __construct($playerId, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $players = $game->getPlayers();
        $gameId = $game->getId();
        $color = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($color);
        $players->activatePlayerTurn($color, $playerId, $gameId, $db); // todo po co to jest?

        if ($player->getComputer()) {
            $player->unfortifyArmies($gameId, $db);
            $type = 'computerStart';
        } else {
            $type = 'startTurn';
        }

        $units = Zend_Registry::get('units');

        $player->getArmies()->resetMovesLeft($gameId, $db);
        $castles = $player->getCastles();
        $isComputer = $player->getComputer();

        foreach ($castles->getKeys() as $castleId) {
            $castle = $player->getCastles()->getCastle($castleId);
            $player->addGold($castle->getIncome());
            $production = $castle->getProduction();

            if ($isComputer) {
                if ($game->getTurnNumber() < 7) {
                    $unitId = $castle->getUnitIdWithShortestProductionTime($production);
                } else {
                    $unitId = $castle->findBestCastleProduction();
                }
                if ($unitId != $castle->getProductionId()) {
                    $relocationToCastleId = null;
                    $castle->setProductionId($gameId, $playerId, $castleId, $unitId, $relocationToCastleId, $db);
                }
            } else {
                $unitId = $castle->getProductionId();
            }

            if ($unitId && $production[$unitId]['time'] <= $castle->getProductionTurn() && $units[$unitId]['cost'] <= $player->getGold()) {
                $castle->resetProductionTurn($gameId, $db);
                $unitCastleId = null;

                if ($relocationCastleId = $castle->getRelocationCastleId()) {
                    if ($castles->hasCastle($relocationCastleId)) {
                        $unitCastleId = $relocationCastleId;
                    }

                    if (!$unitCastleId) {
                        $castle->cancelProductionRelocation($gameId, $db);
                    }
                }

                if (!$unitCastleId) {
                    $unitCastleId = $castleId;
                }

                $x = $castles->getCastle($unitCastleId)->getX();
                $y = $castles->getCastle($unitCastleId)->getY();
                $armyId = $player->getArmies()->getArmyIdFromPosition($x, $y);

                if (!$armyId) {
                    $armyId = $player->createArmy($gameId, $playerId, $x, $y, $db);
                }

                $player->getArmies()->getArmy($armyId)->createSoldier($gameId, $playerId, $unitId, $db);
            }
        }

        $player->saveGold($gameId, $db);

        $token = array(
            'type' => $type,
            'gold' => $player->getGold(),
            'armies' => $player->getArmies()->toArray(),
            'castles' => $player->getCastles()->toArray(),
            'color' => $color
        );
        $gameHandler->sendToChannel($db, $token, $gameId);
    }

}
