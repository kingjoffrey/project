<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
class Cli_Model_StartTurn
{

    public function __construct($playerId, $user, $handler)
    {
        $game = Cli_CommonHandler::getGameFromUser($user);
        $players = $game->getPlayers();
        $color = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($color);
        if ($player->getTurnActive()) {
            return;
        }
        $db = $handler->getDb();
        $gameId = $game->getId();
        $fields = $game->getFields();
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

        $upkeep = 0;
        foreach ($armies->getKeys() as $armyId) {
            $upkeep += $armies->getArmy($armyId)->getCosts();
        }

        $income = $towers->count() * 5;
        foreach ($castles->getKeys() as $castleId) {
            $income += $castles->getCastle($castleId)->getIncome();
        }

        $player->addGold(-$upkeep);
        $player->addGold($income);

        foreach ($castles->getKeys() as $castleId) {
            $castle = $castles->getCastle($castleId);
            $production = $castle->getProduction();

            if ($isComputer) {
                if ($game->getTurnNumber() < 7) {
                    $unitId = $castle->getUnitIdWithShortestProductionTime($production);
                } else {
                    $unitId = $castle->findBestCastleProduction();
                }
                if ($unitId != $castle->getProductionId()) {
                    $relocationToCastleId = null;
                    $castle->setProductionId($unitId, $relocationToCastleId, $playerId, $gameId, $db);
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

        $me = Cli_Model_Me::getMe($user);
        if ($me->getId() == $playerId) {
            $token = array(
                'type' => 'update',
                'upkeep' => $upkeep,
                'gold' => $player->getGold(),
                'income' => $income,
                'productionTurns' => $castles->productionTurnsToArray()
            );
            $handler->sendToUser($user, $token);
        }

        $token = array(
            'type' => 'startTurn',
            'armies' => $armies->toArray(),
            'color' => $color
        );
        $handler->sendToChannel($token);

        if ($player->getComputer()) {
            new Cli_Model_Computer($user, $handler);
        }
    }
}
