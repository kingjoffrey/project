<?php

class Cli_Model_Statistics
{
    public function __construct(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_GameHandler $handler)
    {
        $game = Cli_Model_Game::getGame($user);
        $playersInGameColors = $game->getPlayersColors();
        $db = $handler->getDb();

        $mCastlesConquered = new Application_Model_CastlesConquered($game->getId(), $db);
        $mCastlesDestroyed = new Application_Model_CastlesDestroyed($game->getId(), $db);
        $mHeroesKilled = new Application_Model_HeroesKilled($game->getId(), $db);
        $mSoldiersKilled = new Application_Model_SoldiersKilled($game->getId(), $db);
        $mSoldiersCreated = new Application_Model_SoldiersCreated($game->getId(), $db);

        $castlesConquered = array(
            'winners' => $mCastlesConquered->countConquered($playersInGameColors),
            'losers' => $mCastlesConquered->countLost($playersInGameColors)
        );
        $heroesKilled = array(
            'winners' => $mHeroesKilled->countKilled($playersInGameColors),
            'losers' => $mHeroesKilled->countLost($playersInGameColors)
        );
        $soldiersKilled = array(
            'winners' => $mSoldiersKilled->countKilled($playersInGameColors),
            'losers' => $mSoldiersKilled->countLost($playersInGameColors)
        );
        $soldiersCreated = $mSoldiersCreated->countCreated($playersInGameColors);
        $castlesDestroyed = $mCastlesDestroyed->countAll($playersInGameColors);

        $token = array(
            'type' => 'statistics',
            'castlesConquered' => $castlesConquered,
            'heroesKilled' => $heroesKilled,
            'soldiersKilled' => $soldiersKilled,
            'soldiersCreated' => $soldiersCreated,
            'castlesDestroyed' => $castlesDestroyed
        );

        $handler->sendToUser($user, $token);
    }
}