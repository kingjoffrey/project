<?php

class Game_Cli_Turn {

    static public function next($gameId, $playerId, $db) {
        if (Game_Cli_Database::playerLost($gameId, $playerId, $db)) {

            return;
        }

        $response = array();
        $nextPlayer = array(
            'color' => Game_Cli_Database::getColorByPlayerId($gameId, $playerId, $db)
        );

        while (empty($response))
        {
            $nextPlayer = Game_Cli_Database::getExpectedNextTurnPlayer($gameId, $nextPlayer['color'], $db);
            $playerCastlesExists = Game_Cli_Database::playerCastlesExists($gameId, $nextPlayer['playerId'], $db);
            $playerArmiesExists = Game_Cli_Database::playerArmiesExists($gameId, $nextPlayer['playerId'], $db);
            if ($playerCastlesExists || $playerArmiesExists) {
                $response['color'] = $nextPlayer['color'];

                if ($nextPlayer['playerId'] == $playerId) { // następny gracz to ten sam gracz, który zainicjował zmianę tury
                    $response['win'] = true;
                    Game_Cli_Database::endGame($gameId, $db); // koniec gry
                } else { // zmieniam turę
                    Game_Cli_Database::updateTurnNumber($gameId, $nextPlayer['playerId'], $db);
                    Game_Cli_Database::raiseAllCastlesProductionTurn($gameId, $playerId, $db);
                    $turn = Game_Cli_Database::getTurn($gameId, $db);
                    $response['lost'] = $turn['lost'];
                    $response['nr'] = $turn['nr'];
                }
            } else {
                Game_Cli_Database::setPlayerLostGame($gameId, $nextPlayer['playerId']);
            }
        }

        return $response;
    }

    static public function start($gameId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $castles = array();
        $income = 0;
        $costs = 0;

        Game_Cli_Database::turnActivate($gameId, $playerId, $db);
        Game_Cli_Database::resetHeroesMovesLeft($gameId, $playerId, $db);
        Game_Cli_Database::resetSoldiersMovesLeft($gameId, $playerId, $db);

        $gold = Game_Cli_Database::getPlayerInGameGold($gameId, $playerId, $db);

        if (Game_Cli_Database::getTurnNumber($gameId, $db) > 0) {
            $castlesId = Game_Cli_Database::getPlayerCastles($gameId, $playerId, $db);
            foreach ($castlesId as $id)
            {
                $castleId = $id['castleId'];
                $castles[$castleId] = Application_Model_Board::getCastle($castleId);
                $castle = $castles[$castleId];
                $income += $castle['income'];
                $armyId = Game_Cli_Database::getArmyIdFromPosition($gameId, $castle['position'], $db);
                if (!$armyId) {
                    $armyId = Game_Cli_Database::createArmy($gameId, $db, $castle['position'], $playerId);
                }
                if (!empty($armyId)) {
                    $castleProduction = Game_Cli_Database::getCastleProduction($gameId, $castleId, $playerId, $db);
                    $castles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];
                    $unitName = Application_Model_Board::getUnitName($castleProduction['production']);
                    if ($castleProduction['production'] AND
                            $castle['production'][$unitName]['time'] <= $castleProduction['productionTurn']
                            AND $castle['production'][$unitName]['cost'] <= $gold
                    ) {
                        if (Game_Cli_Database::resetProductionTurn($gameId, $castleId, $playerId, $db) == 1) {
                            Game_Cli_Database::addSoldierToArmy($gameId, $armyId, $castleProduction['production'], $db);
                        }
                    }
                }
            }
        }
        $armies = Game_Cli_Database::getPlayerArmies($gameId, $playerId, $db);
        if (empty($castles) && empty($armies)) {
            return array('gameover' => 1);
        } else {
            $array = array();
            foreach ($armies as $army)
            {
                foreach ($army['soldiers'] as $unit)
                {
                    $costs += $unit['cost'];
                }
                $array['army' . $army['armyId']] = $army;
            }
            $gold = $gold + $income - $costs;
            Game_Cli_Database::updatePlayerInGameGold($gameId, $playerId, $gold, $db);

            return array(
                'gold' => $gold,
                'costs' => $costs,
                'income' => $income,
                'armies' => $array,
                'castles' => $castles,
                'color' => Game_Cli_Database::getColorByPlayerId($gameId, $playerId, $db)
            );
        }
    }

}
