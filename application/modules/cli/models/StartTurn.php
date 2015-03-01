<?php

class Cli_Model_StartTurn
{

    public function __construct($playerId, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        $fields = $game->getFields();
        $players = $game->getPlayers();
        $gameId = $game->getId();
        $color = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($color);
        $armies = $player->getArmies();
        $castles = $player->getCastles();
        $towers = $player->getTowers();
        $isComputer = $player->getComputer();

        // ustawienie informacji o tym, że tura gracza została uruchomiona
        $players->activatePlayerTurn($color, $playerId, $gameId, $db);
        $armies->resetMovesLeft($gameId, $db);
        $castles->increaseAllProductionTurn($playerId, $gameId, $db);

        if ($isComputer) {
            $armies->unfortify();
        }

        foreach ($armies->getKeys() as $armyId) {
            $army = $armies->getArmy($armyId);
            $player->addGold(-$army->getCosts());
        }

        $player->addGold($towers->count() * 5);

        foreach ($castles->getKeys() as $castleId) {
            $castle = $castles->getCastle($castleId);
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
                    $castle->setProductionId($gameId, $playerId, $unitId, $relocationToCastleId, $db);
                }
            } else {
                $unitId = $castle->getProductionId();
            }

            if ($unitId && $production[$unitId]['time'] <= $castle->getProductionTurn() && $player->getGold() > 0) {
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
                $castleArmies = $fields->getField($x, $y)->getArmies();
                $armyId = 0;
                foreach ($castleArmies as $id => $armyColor) {
                    if ($armyColor == $color) {
                        $armyId = $id;
                        break;
                    }
                }

                if (empty($armyId)) {
                    $armyId = $armies->create($x, $y, $color, $game, $db);
                }

                $armies->getArmy($armyId)->createSoldier($gameId, $playerId, $unitId, $db);
            }
        }

        $player->saveGold($gameId, $db);

        $token = array(
            'type' => 'startTurn',
            'gold' => $player->getGold(),
            'armies' => $armies->toArray(),
            'castles' => $castles->toArray(),
            'color' => $color
        );
        $gameHandler->sendToChannel($db, $token, $gameId);
    }

}
