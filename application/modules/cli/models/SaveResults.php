<?php

class Cli_Model_SaveResults
{
    public function __construct(Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $mGame = new Application_Model_Game($game->getId(), $db);
        $mGame->endGame(); // koniec gry

        $mGameScore = new Application_Model_GameScore($game->getId(), $db);
        if ($mGameScore->gameScoreExists()) {
            $token = array(
                'type' => 'end'
            );
            $gameHandler->sendToChannel($game, $token);
            $gameHandler->removeGame($game->getId());
            return;
        }

        $mGameResults = new Application_Model_GameResults($game->getId(), $db);
        $mPlayer = new Application_Model_Player($db);

        $mCastlesConquered = new Application_Model_CastlesConquered($game->getId(), $db);
        $mCastlesDestroyed = new Application_Model_CastlesDestroyed($game->getId(), $db);
        $mHeroesKilled = new Application_Model_HeroesKilled($game->getId(), $db);
        $mSoldiersKilled = new Application_Model_SoldiersKilled($game->getId(), $db);
        $mSoldiersCreated = new Application_Model_SoldiersCreated($game->getId(), $db);
        $mPlayersInGame = new Application_Model_PlayersInGame($game->getId(), $db);
//        $mUnitsInGame = new Application_Model_UnitsInGame($game->getId(), $db);
//        $mHeroesInGame = new Application_Model_HeroesInGame($game->getId(), $db);
//        $mCastlesInGame = new Application_Model_CastlesInGame($game->getId(), $db);

        $playersInGameColors = $game->getPlayersInGameColors();
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
        $gameHandler->sendToChannel($game, $token);
        $gameHandler->removeGame($game->getId());
    }
}

