<?php

class Cli_Model_Statistics
{

    public function __construct($user, $db, $gameHandler)
    {
        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $mCastlesConquered = new Application_Model_CastlesConquered($user->parameters['gameId'], $db);
        $mCastlesDestroyed = new Application_Model_CastlesDestroyed($user->parameters['gameId'], $db);
        $mHeroesKilled = new Application_Model_HeroesKilled($user->parameters['gameId'], $db);
        $mSoldiersKilled = new Application_Model_SoldiersKilled($user->parameters['gameId'], $db);
        $mSoldiersCreated = new Application_Model_SoldiersCreated($user->parameters['gameId'], $db);

        $token = array(
            'type' => 'statistics',
            'castlesConquered' => array(
                'winners' => $mCastlesConquered->countConquered($playersInGameColors),
                'losers' => $mCastlesConquered->countLost($playersInGameColors)
            ),
            'heroesKilled' => array(
                'winners' => $mHeroesKilled->countKilled($playersInGameColors),
                'losers' => $mHeroesKilled->countLost($playersInGameColors)
            ),
            'soldiersKilled' => array(
                'winners' => $mSoldiersKilled->countKilled($playersInGameColors),
                'losers' => $mSoldiersKilled->countLost($playersInGameColors)
            ),
            'soldiersCreated' => $mSoldiersCreated->countCreated($playersInGameColors),
            'castlesDestroyed' => $mCastlesDestroyed->countAll($playersInGameColors)
        );

        $this->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}