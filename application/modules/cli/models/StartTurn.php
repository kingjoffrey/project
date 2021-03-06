<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_StartTurn
{

    public function __construct($playerId, WebSocketTransportInterface $user, $handler)
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
            $army = $armies->getArmy($armyId);
            $upkeep += $army->getCosts();
            $army->regenerateLife($gameId, $db);
        }

        $income = $towers->count() * 5;
        foreach ($castles->getKeys() as $castleId) {
            $income += $castles->getCastle($castleId)->getIncome();
        }

        if ($isComputer) {
            $income += $income;
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
                    $castle->setProductionId($unitId, $playerId, $gameId, $db);
                }
            } else {
                $unitId = $castle->getProductionId();
            }

            if ($unitId && $production[$unitId]['time'] <= $castle->getProductionTurn() && $player->getGold() > 0) {
                $castle->resetProductionTurn($gameId, $db);
                $unitCastleId = null;

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
                'type' => 'yourTurn',
                'gold' => $player->getGold(),
                'productionTurns' => $castles->productionTurnsToArray()
            );

            if ($game->getTurnNumber() % 7 == 0) {
                $token['seven'] = 1;
            }

            $handler->sendToUser($user, $token);
        }

        if ($game->getTurnNumber() % 7 == 0) {
            $playersInGameColors = $game->getPlayersColors();

            reset($playersInGameColors);
            $firstColor = current($playersInGameColors);

            if ($color == $firstColor) {
                foreach ($game->getRuins()->getKeys() as $ruinId) {
                    $ruin = $game->getRuins()->getRuin($ruinId);
                    if ($ruin->getEmpty()) {
                        $ruin->unsetEmpty($gameId, $db);
                    }
                }
            }
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
