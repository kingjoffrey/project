<?php

class Cli_Model_Statistics
{

    private $_castlesConquered;
    private $_heroesKilled;
    private $_soldiersKilled;
    private $_soldiersCreated;
    private $_castlesDestroyed;

    public function __construct($gameId, $db)
    {
        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $mCastlesConquered = new Application_Model_CastlesConquered($gameId, $db);
        $mCastlesDestroyed = new Application_Model_CastlesDestroyed($gameId, $db);
        $mHeroesKilled = new Application_Model_HeroesKilled($gameId, $db);
        $mSoldiersKilled = new Application_Model_SoldiersKilled($gameId, $db);
        $mSoldiersCreated = new Application_Model_SoldiersCreated($gameId, $db);

        $this->_castlesConquered = array(
            'winners' => $mCastlesConquered->countConquered($playersInGameColors),
            'losers' => $mCastlesConquered->countLost($playersInGameColors)
        );
        $this->_heroesKilled = array(
            'winners' => $mHeroesKilled->countKilled($playersInGameColors),
            'losers' => $mHeroesKilled->countLost($playersInGameColors)
        );
        $this->_soldiersKilled = array(
            'winners' => $mSoldiersKilled->countKilled($playersInGameColors),
            'losers' => $mSoldiersKilled->countLost($playersInGameColors)
        );
        $this->_soldiersCreated = $mSoldiersCreated->countCreated($playersInGameColors);
        $this->_castlesDestroyed = $mCastlesDestroyed->countAll($playersInGameColors);
    }

    public function toArray()
    {
        return array(
            'castlesConquered' => $this->_castlesConquered,
            'heroesKilled' => $this->_heroesKilled,
            'soldiersKilled' => $this->_soldiersKilled,
            'soldiersCreated' => $this->_soldiersCreated,
            'castlesDestroyed' => $this->_castlesDestroyed
        );
    }
}