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
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $gameId);

        return $this->selectOne($select);
    }

    public function getPlayerScores($playerId)
    {
        $gameId = $this->_db->quoteIdentifier('gameId');
        $mapId = $this->_db->quoteIdentifier('mapId');

        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'score')
            ->join(array('b' => 'game'), 'a.' . $gameId . ' = b.' . $gameId, array('gameId', 'begin', 'end', 'turnNumber', 'numberOfPlayers'))
            ->join(array('c' => 'map'), 'b.' . $mapId . ' = c.' . $mapId, array('name'))
            ->where('a.' . $this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
            ->where('tutorial = ?', $this->parseBool(false));

        return $this->selectAll($select);
    }

    public function get($gameId)
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $gameId)
            ->order('score desc');

        return $this->selectAll($select);
    }
}

