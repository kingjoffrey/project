<?php

class Application_Model_GameScore extends Coret_Db_Table_Abstract
{
    protected $_name = 'gamescore';
    protected $_primary = 'gamescoreId';
    protected $_sequence = 'gamescore_gamescoreId_seq';
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

    public function add($playerId, $playerScore)
    {
        $data = array(
            'gameId' => $this->_gameId,
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
            'score' => $playerScore['score']
        );

        $this->insert($data);
    }

    public function gameScoreExists()
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where('"gameId" = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function getPlayer($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where('"playerId" = ?', $playerId)
            ->where('"gameId" = ?', $this->_gameId);

        return $this->selectAll($select);
    }

    public function get()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where('"gameId" = ?', $this->_gameId)
            ->order('score desc');

        return $this->selectAll($select);
    }
}

