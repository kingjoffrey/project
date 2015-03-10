<?php

class Cli_Model_SaveResults
{
    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $mGame = new Application_Model_Game($gameId, $this->_db);
        $mGame->endGame(); // koniec gry

        $mGameScore = new Application_Model_GameScore($gameId, $db);
        if ($mGameScore->gameScoreExists()) {
            return;
        }

        $mGameResults = new Application_Model_GameResults($gameId, $db);
        $mPlayer = new Application_Model_Player($db);

        $mCastlesConquered = new Application_Model_CastlesConquered($gameId, $db);
        $mCastlesDestroyed = new Application_Model_CastlesDestroyed($gameId, $db);
        $mHeroesKilled = new Application_Model_HeroesKilled($gameId, $db);
        $mSoldiersKilled = new Application_Model_SoldiersKilled($gameId, $db);
        $mSoldiersCreated = new Application_Model_SoldiersCreated($gameId, $db);
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
//        $mUnitsInGame = new Application_Model_UnitsInGame($gameId, $db);
//        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
//        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);

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
                    $points['soldiersCreated'] += $units->getUnit($unitId)->getAttackPoints() + $units->getUnit($unitId)->getDefensePoints();
                }
            }
            $sumPoints += $points['soldiersCreated'];

            $playerSoldiersKilled = 0;
            $points['soldiersKilled'] = 0;
            if (isset($soldiersKilled[$playerId])) {
                foreach ($soldiersKilled[$playerId] as $unitId) {
                    $playerSoldiersKilled++;
                    $points['soldiersKilled'] += $units->getUnit($unitId)->getAttackPoints() + $units->getUnit($unitId)->getDefensePoints();
                }
            }
            $sumPoints += $points['soldiersKilled'];

            $playerSoldiersLost = 0;
            $points['soldiersLost'] = 0;
            if (isset($soldiersLost[$playerId])) {
                foreach ($soldiersLost[$playerId] as $unitId) {
                    $playerSoldiersLost++;
                    $points['soldiersLost'] -= $units->getUnit($unitId)->getAttackPoints();
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

        $token = array(
            'type' => 'end'
        );
        $gameHandler->sendToChannel($db, $token, $gameId);
    }
}

