<?php

class Cli_Model_SaveResults
{
    public function __construct(Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mGame = new Application_Model_Game($game->getId(), $db);
        $mGame->endGame(); // koniec gry

        $mGameScore = new Application_Model_GameScore($db);
        if ($mGameScore->gameScoreExists($game->getId())) {
            return;
        }

        $teamScores = array();

        $mGameResults = new Application_Model_GameAchievements($game->getId(), $db);
        $mPlayer = new Application_Model_Player($db);

        $mCastlesConquered = new Application_Model_CastlesConquered($game->getId(), $db);
        $mCastlesDestroyed = new Application_Model_CastlesDestroyed($game->getId(), $db);
        $mHeroesKilled = new Application_Model_HeroesKilled($game->getId(), $db);
        $mSoldiersKilled = new Application_Model_SoldiersKilled($game->getId(), $db);
        $mSoldiersCreated = new Application_Model_SoldiersCreated($game->getId(), $db);
        $mPlayersInGame = new Application_Model_PlayersInGame($game->getId(), $db);

        $playersInGameColors = $game->getPlayersColors();
        $units = Zend_Registry::get('units');

        $castlesConquered = $mCastlesConquered->countConquered($playersInGameColors);
        $castlesLost = $mCastlesConquered->countLost($playersInGameColors);

        $heroesKilled = $mHeroesKilled->countKilled($playersInGameColors);
        $heroesLost = $mHeroesKilled->countLost($playersInGameColors);


        $soldiersKilled = $mSoldiersKilled->getKilled();
        $soldiersLost = $mSoldiersKilled->getLost();

        $soldiersCreated = $mSoldiersCreated->getCreated();

        $castlesDestroyed = $mCastlesDestroyed->countAll($playersInGameColors);

        foreach ($playersInGameColors as $playerId => $shortName) {
            $points = array();
            $sumPoints = 0;
            $player = $game->getPlayers()->getPlayer($shortName);

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

            $points['gold'] = $player->getGold();
            $sumPoints += $points['gold'];

            $mGameResults->add(
                $playerId,
                $playerCastlesConquered,
                $playerCastlesLost,
                $playerCastlesDestroyed,
                $playerSoldiersCreated,
                $playerSoldiersKilled,
                $playerSoldiersLost,
                $playerHeroesKilled,
                $playerHeroesLost
            );

            $armies = $player->getArmies();

            $points['heroes'] = 0;
            $points['soldiers'] = 0;

            foreach ($armies->getKeys() as $armyId) {
                $army = $armies->getArmy($armyId);
                $points['heroes'] += $army->getHeroes()->count() * 100;
                $points['soldiers'] += $army->getSwimmingSoldiers()->getCosts();
                $points['soldiers'] += $army->getFlyingSoldiers()->getCosts();
                $points['soldiers'] += $army->getWalkingSoldiers()->getCosts();
            }

            $sumPoints += $points['heroes'];
            $sumPoints += $points['soldiers'];

            $points['score'] = $sumPoints;

            $mGameScore->add($game->getId(), $playerId, $points);

            $teamId = $player->getTeamId();
            if (!isset($teamScores[$teamId])) {
                $teamScores[$teamId] = 0;
            }
            $teamScores[$teamId] += $sumPoints;

            if ($game->getType() == 3) {
                $mPlayer->addScore($playerId, $sumPoints);
            }
        }

        $winners = null;
        $bestScore = 0;

        foreach ($teamScores as $teamId => $score) {
            if ($score > $bestScore) {
                $bestScore = $score;
                $winners = $teamId;
            }
        }

        if ($game->getType() == 3) {
            $mTournamentPlayers = new Application_Model_TournamentPlayers($db);
            $mTournamentGames = new Application_Model_TournamentGames($db);
        }

        foreach ($playersInGameColors as $playerId => $shortName) {
            $player = $game->getPlayers()->getPlayer($shortName);
            if ($player->getTeamId() != $winners) {
                $mPlayersInGame->setLost($playerId);
            } elseif ($game->getType() == 3) {
                $mTournamentPlayers->updateStage($mTournamentGames->getTournamentId($game->getId()), $playerId);
            }
        }
    }

    public function sendToken($handler)
    {
        $token = array(
            'type' => 'end'
        );
        $handler->sendToChannel($token);
    }
}

