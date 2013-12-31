<?php

class Cli_Model_Turn
{
    private $_db;

    public function __construct($user, $db, $gameHandler)
    {
        $this->_db = $db;
        $this->_gameHandler = $gameHandler;
        $this->_user = $user;
    }

    public function next($playerId)
    {
        if ($this->_user->parameters['turnsLimit']) {
            $mGame = new Application_Model_Game($this->_user->parameters['gameId'], $this->_db);
            $turnNumber = $mGame->getTurnNumber();
            if ($turnNumber >= $this->_user->parameters['turnsLimit']) {
                $this->_gameHandler->sendError($this->_user, '!');
                return;
            }
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($this->_user->parameters['gameId'], $this->_db);

        if ($mPlayersInGame->playerLost($playerId)) {
            echo 'vvv111';
//            return;
        }

        $playersInGameColors = Zend_Registry::get('playersInGameColors');
        $mArmy = new Application_Model_Army($this->_user->parameters['gameId'], $this->_db);
        $mCastlesInGame = new Application_Model_CastlesInGame($this->_user->parameters['gameId'], $this->_db);

        $playerCastlesExists = $mCastlesInGame->playerCastlesExists($playerId);
        $playerArmiesExists = $mArmy->playerArmiesExists($playerId);
        if (!$playerCastlesExists && !$playerArmiesExists) {
            $token = array(
                'type' => 'dead',
                'color' => $playersInGameColors[$playerId]
            );
            $this->_gameHandler->sendToChannel($this->_db, $token, $this->_user->parameters['gameId']);
            $mPlayersInGame->setPlayerLostGame($playerId);
        }

        $nextPlayerId = $playerId;

        if (!isset($mGame)) {
            $mGame = new Application_Model_Game($this->_user->parameters['gameId'], $this->_db);
        }
        while (true) {
            $nextPlayerId = $this->getExpectedNextTurnPlayer($playersInGameColors[$nextPlayerId]);
            $playerCastlesExists = $mCastlesInGame->playerCastlesExists($nextPlayerId);
            $playerArmiesExists = $mArmy->playerArmiesExists($nextPlayerId);
            if ($playerCastlesExists || $playerArmiesExists) {
                if ($nextPlayerId == $playerId) { // następny gracz to ten sam gracz, który zainicjował zmianę tury
                    $mGame->endGame(); // koniec gry
                    $this->saveResults();

                    $token = array(
                        'type' => 'end'
                    );

                    $this->_gameHandler->sendToChannel($this->_db, $token, $this->_user->parameters['gameId']);
                    return;
                } else { // zmieniam turę
                    $mGame->updateTurnNumber($nextPlayerId, $playersInGameColors[$nextPlayerId]);
                    $mCastlesInGame->increaseAllCastlesProductionTurn($nextPlayerId);

                    $turnNumber = $mGame->getTurnNumber();

                    if ($this->_user->parameters['turnsLimit'] && $turnNumber >= $this->_user->parameters['turnsLimit']) {
                        $mGame->endGame(); // koniec gry
                        $this->saveResults();

                        $token = array(
                            'type' => 'end'
                        );

                        $this->_gameHandler->sendToChannel($this->_db, $token, $this->_user->parameters['gameId']);
                        return;
                    }

                    $token = array(
                        'type' => 'nextTurn',
                        'nr' => $turnNumber,
                        'color' => $playersInGameColors[$nextPlayerId]
                    );
                    $mTurnHistory = new Application_Model_TurnHistory($this->_user->parameters['gameId'], $this->_db);
                    $date = $mTurnHistory->add($nextPlayerId, $token['nr']);

                    $this->_gameHandler->sendToChannel($this->_db, $token, $this->_user->parameters['gameId']);

                    return $date;
                }
            } else {
                $token = array(
                    'type' => 'dead',
                    'color' => $playersInGameColors[$nextPlayerId]
                );
                $this->_gameHandler->sendToChannel($this->_db, $token, $this->_user->parameters['gameId']);
                $mPlayersInGame->setPlayerLostGame($nextPlayerId);
            }
        }
    }

    private function getExpectedNextTurnPlayer($playerColor)
    {
        $find = false;
        $playersInGameColors = Zend_Registry::get('playersInGameColors');
        reset($playersInGameColors);
        $firstColor = current($playersInGameColors);

        /* szukam następnego koloru w dostępnych kolorach */
        foreach ($playersInGameColors as $color) {
            /* znajduję kolor gracza, który ma aktualnie turę i przewijam na następny */
            if ($playerColor == $color) {
                $find = true;
                continue;
            }

            /* to jest przewinięty kolor gracza */
            if ($find) {
                $nextPlayerColor = $color;
                break;
            }
        }

        /* jeśli nie znalazłem następnego gracza to następnym graczem jest gracz pierwszy */
        if (!isset($nextPlayerColor)) {
            $nextPlayerColor = $firstColor;
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($this->_user->parameters['gameId'], $this->_db);
        $playersInGame = $mPlayersInGame->getPlayersInGame();

        /* przypisuję playerId do koloru */
        foreach ($playersInGame as $player) {
            if ($player['color'] == $nextPlayerColor) {
                if ($player['color'] == $firstColor) {
                    $mGame = new Application_Model_Game($this->_user->parameters['gameId'], $this->_db);
                    $mGame->updateTurnNumber($player['playerId'], $player['color']);
                }
                return $player['playerId'];
            }
        }

        throw new Exception('czy ten kod jest potrzebny?');

//        /* jeśli nie znalazłem następnego gracza to następnym graczem jest gracz pierwszy */
//        foreach ($playersInGame as $k => $player) {
//            if ($player['color'] == $firstColor) {
//                $mGame = new Application_Model_Game($this->_user->parameters['gameId'], $this->_db);
//                $mGame->updateTurnNumber($player['playerId'], $player['color']);
//
//                if ($player['lost']) {
//                    return $playersInGame[$k + 1]['playerId'];
//                } else {
//                    return $player['playerId'];
//                }
//            }
//        }
//
//        $l = new Coret_Model_Logger('cli');
//        $l->log('Błąd! Nie znalazłem gracza');
//
//        return;
    }

    public function start($playerId, $computer = null)
    {
        $playersInGameColors = Zend_Registry::get('playersInGameColors');
        $color = $playersInGameColors[$playerId];

        $mPlayersInGame = new Application_Model_PlayersInGame($this->_user->parameters['gameId'], $this->_db);
        $mPlayersInGame->turnActivate($playerId);

        $mArmy = new Application_Model_Army($this->_user->parameters['gameId'], $this->_db);
        $mSoldier = new Application_Model_UnitsInGame($this->_user->parameters['gameId'], $this->_db);
        $mSoldier->resetMovesLeft($mArmy->getSelectForPlayerAll($playerId));

        $gold = $mPlayersInGame->getPlayerGold($playerId);
        if ($computer) {
            $mArmy->unfortifyComputerArmies($playerId);
            $type = 'computerStart';
        } else {
            $type = 'startTurn';
        }
        $mHeroesInGame = new Application_Model_HeroesInGame($this->_user->parameters['gameId'], $this->_db);
        $mHeroesInGame->resetMovesLeftForAll($playerId);

        $income = 0;

        $mapCastles = Zend_Registry::get('castles');
        $units = Zend_Registry::get('units');

        $mCastlesInGame = new Application_Model_CastlesInGame($this->_user->parameters['gameId'], $this->_db);
        $playerCastles = $mCastlesInGame->getPlayerCastles($playerId);

        $mSoldier = new Application_Model_UnitsInGame($this->_user->parameters['gameId'], $this->_db);
        $mSoldiersCreated = new Application_Model_SoldiersCreated($this->_user->parameters['gameId'], $this->_db);

        foreach ($playerCastles as $castleId => $castleInGame) {
            $income += $mapCastles[$castleId]['income'];

            $castleProduction = $mCastlesInGame->getProduction($castleId, $playerId);
            $playerCastles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];

            if ($computer) {
                if (!isset($turnNumber)) {
                    $mGame = new Application_Model_Game($this->_user->parameters['gameId'], $this->_db);
                    $turnNumber = $mGame->getTurnNumber();
                }

                if ($turnNumber < 7) {
                    $unitId = Application_Model_Board::getUnitIdWithMinimalProductionTime($mapCastles[$castleId]['production']);
                } else {
                    $unitId = Cli_Model_Army::findBestCastleProduction($units, $mapCastles[$castleId]['production']);
                }

                if ($unitId != $castleProduction['productionId']) {
                    $mCastlesInGame->setProduction($playerId, $castleId, $unitId);
                    $castleProduction = $mCastlesInGame->getProduction($castleId, $playerId);
                }
            } else {
                $unitId = $castleProduction['productionId'];
            }

            if ($unitId && $mapCastles[$castleId]['production'][$unitId]['time'] <= $castleProduction['productionTurn'] && $units[$unitId]['cost'] <= $gold) {
                if ($mCastlesInGame->resetProductionTurn($castleId, $playerId) == 1) {
                    $unitCastleId = null;
                    if ($castleProduction['relocationCastleId']) {
                        foreach ($playerCastles as $castle) {
                            if ($castleProduction['relocationCastleId'] == $castle['castleId']) {
                                $unitCastleId = $castleProduction['relocationCastleId'];
                                break;
                            }
                        }

                        if (!$unitCastleId) {
                            $mCastlesInGame->cancelProductionRelocation($playerId, $castleId);
                        }
                    }

                    if (!$unitCastleId) {
                        $unitCastleId = $castleId;
                    }

                    $armyId = $mArmy->getArmyIdFromPosition($mapCastles[$unitCastleId]['position']);

                    if (!$armyId) {
                        $armyId = $mArmy->createArmy($mapCastles[$unitCastleId]['position'], $playerId);
                    }

                    $mSoldier->add($armyId, $unitId);
                    $mSoldiersCreated->add($unitId, $playerId);
                }
            }
        }

        $armies = $mArmy->getPlayerArmiesWithUnits($playerId);

        $costs = $mSoldier->calculateCostsOfSoldiers($mArmy->getSelectForPlayerAll($playerId));
        $mTowersInGame = new Application_Model_TowersInGame($this->_user->parameters['gameId'], $this->_db);
        $income += $mTowersInGame->calculateIncomeFromTowers($playerId);
        $gold = $gold + $income - $costs;

        $mPlayersInGame->updatePlayerGold($playerId, $gold);

        $token = array(
            'type' => $type,
            'gold' => $gold,
            'costs' => $costs,
            'income' => $income,
            'armies' => $armies,
            'castles' => $playerCastles,
            'color' => $color
        );
        $this->_gameHandler->sendToChannel($this->_db, $token, $this->_user->parameters['gameId']);
    }

    public function saveResults()
    {
        $mGameScore = new Application_Model_GameScore($this->_user->parameters['gameId'], $this->_db);

        if ($mGameScore->gameScoreExists()) {
            return;
        }

        $mGameResults = new Application_Model_GameResults($this->_user->parameters['gameId'], $this->_db);
        $mPlayer = new Application_Model_Player($this->_db);

        $mCastlesConquered = new Application_Model_CastlesConquered($this->_user->parameters['gameId'], $this->_db);
        $mCastlesDestroyed = new Application_Model_CastlesDestroyed($this->_user->parameters['gameId'], $this->_db);
        $mHeroesKilled = new Application_Model_HeroesKilled($this->_user->parameters['gameId'], $this->_db);
        $mSoldiersKilled = new Application_Model_SoldiersKilled($this->_user->parameters['gameId'], $this->_db);
        $mSoldiersCreated = new Application_Model_SoldiersCreated($this->_user->parameters['gameId'], $this->_db);
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_user->parameters['gameId'], $this->_db);
        $mUnitsInGame = new Application_Model_UnitsInGame($this->_user->parameters['gameId'], $this->_db);
        $mHeroesInGame = new Application_Model_HeroesInGame($this->_user->parameters['gameId'], $this->_db);
        $mCastlesInGame = new Application_Model_CastlesInGame($this->_user->parameters['gameId'], $this->_db);

        $playersInGameColors = Zend_Registry::get('playersInGameColors');
        $units = Zend_Registry::get('units');

        $castlesConquered = $mCastlesConquered->countConquered($playersInGameColors);
        $castlesLost = $mCastlesConquered->countLost($playersInGameColors);

        $heroesKilled = $mHeroesKilled->countKilled($playersInGameColors);
        $heroesLost = $mHeroesKilled->countLost($playersInGameColors);


        $soldiersKilled = $mSoldiersKilled->countKilled($playersInGameColors);
        $soldiersLost = $mSoldiersKilled->countLost($playersInGameColors);

        $soldiersCreated = $mSoldiersCreated->getCreated();

        $castlesDestroyed = $mCastlesDestroyed->countAll($playersInGameColors);

        $playersGold = $mPlayersInGame->getAllPlayersGold();

        foreach ($playersInGameColors as $playerId => $shortName) {
            $points = array();
            $sumPoints = 0;

            if (isset($castlesConquered[$shortName])) {
                $playerCastlesConquered = $castlesConquered[$shortName];
            } else {
                $playerCastlesConquered = 0;
            }

            $points['castlesConquered'] = $playerCastlesConquered * 100;
            $sumPoints += $points['castlesConquered'];

            if (isset($castlesLost[$shortName])) {
                $playerCastlesLost = $castlesLost[$shortName];
            } else {
                $playerCastlesLost = 0;
            }

            $points['castlesLost'] = -($playerCastlesLost * 100);
            $sumPoints += $points['castlesLost'];

            if (isset($castlesDestroyed[$shortName])) {
                $playerCastlesDestroyed = $castlesDestroyed[$shortName];
            } else {
                $playerCastlesDestroyed = 0;
            }

            $points['castlesDestroyed'] = -($playerCastlesDestroyed * 100);
            $sumPoints += $points['castlesDestroyed'];

            $playerSoldiersCreated = 0;
            $points['soldiersCreated'] = 0;
            if (isset($soldiersCreated[$playerId])) {
                foreach ($soldiersCreated[$playerId] as $unitId) {
                    $playerSoldiersCreated++;
                    $points['soldiersCreated'] += $units[$unitId]['attackPoints'] + $units[$unitId]['defensePoints'];
                }
            }
            $sumPoints += $points['soldiersCreated'];

            $playerSoldiersKilled = 0;
            $points['soldiersKilled'] = 0;
            if (isset($soldiersKilled[$playerId])) {
                foreach ($soldiersKilled[$playerId] as $unitId) {
                    $playerSoldiersKilled++;
                    $points['soldiersKilled'] += $units[$unitId]['attackPoints'] + $units[$unitId]['defensePoints'];
                }
            }
            $sumPoints += $points['soldiersKilled'];

            $playerSoldiersLost = 0;
            $points['soldiersLost'] = 0;
            if (isset($soldiersLost[$playerId])) {
                foreach ($soldiersLost[$playerId] as $unitId) {
                    $playerSoldiersLost++;
                    $points['soldiersLost'] -= $units[$unitId]['attackPoints'];
                }
            }

            if (isset($heroesKilled[$shortName])) {
                $playerHeroesKilled = $heroesKilled[$shortName];
            } else {
                $playerHeroesKilled = 0;
            }

            $points['heroesKilled'] = $playerHeroesKilled * 10;
            $sumPoints += $points['heroesKilled'];

            if (isset($heroesLost[$shortName])) {
                $playerHeroesLost = $heroesLost[$shortName];
            } else {
                $playerHeroesLost = 0;
            }

            $points['heroesLost'] = -($playerHeroesLost * 10);
            $sumPoints += $points['heroesLost'];

            $points['gold'] = $playersGold[$playerId];
            $sumPoints += $points['gold'];
            $points['score'] = $sumPoints;

            $mGameResults->add(
                $playerId,
                $playerCastlesConquered,
                $playerCastlesLost,
                $playerCastlesDestroyed,
                $playerSoldiersCreated,
                $playerSoldiersKilled,
                $playerSoldiersLost,
                $playerHeroesKilled,
                $playerHeroesLost,
                $playersGold[$playerId],
                0, 0, 0
            );

            $mGameScore->add($playerId, $points);

            $mPlayer->addScore($playerId, $sumPoints);
        }

    }
}
