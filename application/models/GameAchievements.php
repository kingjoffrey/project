<?php

class Application_Model_GameAchievements extends Coret_Db_Table_Abstract
{
    protected $_name = 'gameachievements';
    protected $_primary = 'gameresultsId';
    protected $_sequence = 'gameresults_gameresultsId_seq';
    protected $_gameId;

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function add($playerId, $castlesConquered, $castlesLost, $castlesDestroyed, $soldiersCreated, $soldiersKilled, $soldiersLost, $heroesKilled, $heroesLost)
    {
        $data = array(
            'gameId' => $this->_gameId,
            'playerId' => $playerId,
            'castlesConquered' => $castlesConquered,
            'castlesLost' => $castlesLost,
            'castlesDestroyed' => $castlesDestroyed,
            'soldiersCreated' => $soldiersCreated,
            'soldiersKilled' => $soldiersKilled,
            'soldiersLost' => $soldiersLost,
            'heroesKilled' => $heroesKilled,
            'heroesLost' => $heroesLost
        );

        $this->insert($data);
    }
}

