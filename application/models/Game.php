<?php

class Application_Model_Game extends Coret_Db_Table_Abstract
{

    protected $_name = 'game';
    protected $_primary = 'gameId';
    protected $_sequence = "game_gameId_seq";
    protected $_gameId;

    public function __construct($gameId = 0, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function createGame($params, $playerId)
    {
        if (!isset($params['turnsLimit'])) {
            $params['turnsLimit'] = 0;
        }
        if (!isset($params['turnTimeLimit'])) {
            $params['turnTimeLimit'] = 0;
        }
        if (!isset($params['timeLimit'])) {
            $params['timeLimit'] = 0;
        }

        $data = array(
            'numberOfPlayers' => $params['numberOfPlayers'],
            'gameMasterId' => $playerId,
            'mapId' => $params['mapId'],
            'turnsLimit' => $params['turnsLimit'],
            'turnTimeLimit' => $params['turnTimeLimit'],
            'timeLimit' => $params['timeLimit'],
            'type' => $params['type']
        );

        $this->_db->insert($this->_name, $data);
        $this->_gameId = $this->_db->lastSequenceId($this->_db->quoteIdentifier($this->_sequence));
        return $this->_gameId;
    }

    public function getOpen($gameMasterId)
    {
        $mapId = $this->_db->quoteIdentifier('mapId');
        $select = $this->_db->select()
            ->from(array('a' => $this->_name))
            ->join(array('b' => 'map'), 'a.' . $mapId . ' = b.' . $mapId, 'name')
            ->where('"isOpen" = true')
            ->where('a.' . $this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('gameMasterId') . ' = ?', $gameMasterId)
            ->order('begin DESC');
        return $this->selectRow($select);
    }

    public function getMyGames($playerId, $mPlayersInGame)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('turnNumber', $this->_primary, 'numberOfPlayers', 'end', 'turnPlayerId', 'mapId'))
            ->join(array('b' => 'playersingame'), 'a."gameId" = b."gameId"', null)
            ->join(array('c' => 'map'), 'a."mapId" = c."mapId"', 'name')
            ->where('"isOpen" = false')
            ->where('"isActive" = true')
            ->where('c."tutorial" = false')
            ->where('a."gameId" IN ?', $mPlayersInGame->getSelectForMyGames($playerId))
            ->where('b."playerId" = ?', $playerId)
            ->order('begin DESC');

        return $this->selectAll($select);
    }

    public function getMyTutorial($playerId)
    {
        $mapId = $this->_db->quoteIdentifier('mapId');

        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('gameId'))
            ->join(array('b' => 'playersingame'), 'a."gameId" = b."gameId"', null)
            ->join(array('c' => 'map'), 'a.' . $mapId . ' = c.' . $mapId, null)
            ->where('"isOpen" = false')
            ->where('"isActive" = true')
            ->where('b."playerId" = ?', $playerId)
            ->where('c."tutorial" = true');
        return $this->selectOne($select);
    }

    public function isTutorial()
    {
        $mapId = $this->_db->quoteIdentifier('mapId');

        $select = $this->_db->select()
            ->from(array('a' => $this->_name), null)
            ->join(array('b' => 'map'), 'a.' . $mapId . ' = b.' . $mapId, 'tutorial')
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function startGame($turnPlayerId)
    {
        $data = array(
            'turnPlayerId' => $turnPlayerId,
            'isOpen' => 'false',
            'begin' => new Zend_Db_Expr('now()')
        );

        $this->updateGame($data);
    }

    public function getGame()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_gameId);

        return $this->selectRow($select);
    }

    public function getGameMasterId()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'gameMasterId')
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_gameId);
        return $this->selectOne($select);
    }

    public function updateGame($data)
    {
        $where = $this->_db->quoteInto($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_gameId);
        return $this->update($data, $where);
    }

    public function getTurnPlayerId()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'turnPlayerId')
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function updateTurn($nextPlayerId, $turnNumber)
    {
        $data = array(
            'turnNumber' => $turnNumber,
            'end' => new Zend_Db_Expr('now()'),
            'turnPlayerId' => $nextPlayerId
        );
        $this->updateGame($data);
    }

    public function endGame()
    {
        $data['isActive'] = 'false';

        $this->updateGame($data);
    }

    public function getMapId()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'mapId')
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_gameId);
        return $this->_db->fetchOne($select);
    }
}
