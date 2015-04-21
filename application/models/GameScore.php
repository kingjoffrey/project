<?php

class Application_Model_GameScore extends Coret_Db_Table_Abstract
{
    protected $_name = 'gamescore';
    protected $_primary = 'gamescoreId';
    protected $_sequence = 'gamescore_gamescoreId_seq';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function add($gameId, $playerId, $playerScore)
    {
        $data = array(
            'gameId' => $gameId,
            'playerId' => $playerId,
            'castlesConquered' => $playerScore['castlesConquered'],
            'castlesLost' => $playerScore['castlesLost'],
            'castlesDestroyed' => $playerScore['castlesDestroyed'],
            'soldiersCreated' => $playerScore['soldiersCreated'],
            'soldiersKilled' => $playerScore['soldiersKilled'],
            'soldiersLost' => $playerScore['soldiersLost'],
            'heroesKilled' => $playerScore['heroesKilled'],
            'heroesLost' => $playerScore['heroesLost'],
            'gold' => $playerScore['gold'],
            'heroes' => $playerScore['heroes'],
            'soldiers' => $playerScore['soldiers'],
            'score' => $playerScore['score']
        );

        $this->insert($data);
    }

    public function gameScoreExists($gameId)
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where('"gameId" = ?', $gameId);

        return $this->selectOne($select);
    }

    public function getPlayerScores($playerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'score')
            ->join(array('b' => 'game'), 'a."gameId" = b."gameId"', array('gameId', 'begin', 'end', 'turnNumber'))
            ->where('"playerId" = ?', $playerId);

        return $this->selectAll($select);
    }

    public function get($gameId)
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where('"gameId" = ?', $gameId)
            ->order('score desc');

        return $this->selectAll($select);
    }
}

