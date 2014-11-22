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
        if ($this->_user->parameters['game']->noPlayerArmiesAndCastles($playerId)) {
            $this->playerLost($playerId);
        }

        $nextPlayerId = $playerId;

        if ($this->_user->parameters['game']->allEnemiesAreDead($playerId)) {
            $this->endGame($this->_user->parameters['gameId']);
            return;
        }

        while (true) {
            $nextPlayerId = $this->_user->parameters['game']->getExpectedNextTurnPlayer($nextPlayerId, $this->_db);
            if ($this->_user->parameters['game']->playerArmiesOrCastlesExists($nextPlayerId)) {

                $this->_user->parameters['game']->increaseAllCastlesProductionTurn($nextPlayerId, $this->_db);

                $turnNumber = $this->_user->parameters['game']->getTurnNumber();
                $turnsLimit = $this->_user->parameters['game']->getTurnsLimit();

                if ($turnsLimit && $turnNumber > $turnsLimit) {
                    $this->endGame($this->_user->parameters['gameId']);
                    return;
                }

                $mTurnHistory = new Application_Model_TurnHistory($this->_user->parameters['gameId'], $this->_db);
                $mTurnHistory->add($nextPlayerId, $turnNumber);

                $this->_user->parameters['game']->setTurnPlayerId($nextPlayerId);

                $token = array(
                    'type' => 'nextTurn',
                    'nr' => $turnNumber,
                    'color' => $this->_user->parameters['game']->getPlayerColor($nextPlayerId)
                );
                $this->_gameHandler->sendToChannel($this->_db, $token, $this->_user->parameters['gameId']);
                return;
            } else {
                $this->playerLost($nextPlayerId);
            }
        }
    }

    private function playerLost($playerId)
    {
        $this->_user->parameters['game']->setPlayerLost($playerId, $this->_db);
        $token = array(
            'type' => 'dead',
            'color' => $this->_user->parameters['game']->getPlayerColor($playerId)
        );
        $this->_gameHandler->sendToChannel($this->_db, $token, $this->_user->parameters['gameId']);

    }

    public function start($playerId, $computer = null)
    {
        $game = $this->_user->parameters['game'];
        $gameId = $game->getId();
        $player = $game->getPlayer($playerId);
        $game->activatePlayerTurn($playerId, $this->_db);

        if ($computer) {
            $player->unfortifyArmies($gameId, $this->_db);
            $type = 'computerStart';
        } else {
            $type = 'startTurn';
        }

        $player->startTurn($gameId, $game->getTurnNumber(), $this->_db);

        $token = array(
            'type' => $type,
            'gold' => $player->getGold(),
            'armies' => $player->armiesToArray(),
            'castles' => $player->castlesToArray(),
            'color' => $game->getPlayerColor($playerId)
        );
        $this->_gameHandler->sendToChannel($this->_db, $token, $gameId);
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
//        $mUnitsInGame = new Application_Model_UnitsInGame($this->_user->parameters['gameId'], $this->_db);
//        $mHeroesInGame = new Application_Model_HeroesInGame($this->_user->parameters['gameId'], $this->_db);
//        $mCastlesInGame = new Application_Model_CastlesInGame($this->_user->parameters['gameId'], $this->_db);

        $playersInGameColors = Zend_Registry::get('playersInGameColors');
        $units = Zend_Registry::get('units');

        $castlesConquered = $mCastlesConquered->countConquered($playersInGameColors);
        $castlesLost = $mCastlesConquered->countLost($playersInGameColors);

        $heroesKilled = $mHeroesKilled->countKilled($playersInGameColors);
        $heroesLost = $mHeroesKilled->countLost($playersInGameColors);


        $soldiersKilled = $mSoldiersKilled->getKilled();
        $soldiersLost = $mSoldiersKilled->getLost();

        $soldiersCreated = $mSoldiersCreated->getCreated();

        $castlesDestroyed = $mCastlesDestroyed->countAll($playersInGameColors);

        $playersGold = $mPlayersInGame->getGoldForAllPlayers();

        foreach ($playersInGameColors as $playerId => $shortName) {
            $points = array();
            $sumPoints = 0;

            if (isset($castlesConquered[$shortName])) {
                $playerCastlesConquered = $castlesConquered[$shortName] - 1;
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

    public function endGame($gameId)
    {
        $mGame = new Application_Model_Game($gameId, $this->_db);
        $mGame->endGame(); // koniec gry
        $this->saveResults();

        $token = array(
            'type' => 'end'
        );

        $this->_gameHandler->sendToChannel($this->_db, $token, $this->_user->parameters['gameId']);

    }
}
