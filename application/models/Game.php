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
        $data = array(
            'numberOfPlayers' => $params['numberOfPlayers'],
            'gameMasterId' => $playerId,
            'mapId' => $params['mapId'],
            'turnsLimit' => $params['turnsLimit'],
            'turnTimeLimit' => $params['turnTimeLimit'],
            'timeLimit' => $params['timeLimit'],
        );

        $this->_db->insert($this->_name, $data);
        return $this->_db->lastSequenceId($this->_db->quoteIdentifier($this->_sequence));
    }

    public function getOpen($gameMasterId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name))
            ->join(array('b' => 'map'), 'a.' . $this->_db->quoteIdentifier('mapId') . '=b.' . $this->_db->quoteIdentifier('mapId'), 'name')
            ->where('"isOpen" = true')
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('gameMasterId') . ' = ?', $gameMasterId)
            ->order('begin DESC');
        return $this->selectRow($select);
    }

    public function getMyGames($playerId, $pageNumber)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId);
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('gameMasterId', 'turnNumber', $this->_primary, 'numberOfPlayers', 'begin', 'end', 'turnsLimit', 'turnTimeLimit', 'timeLimit', 'turnPlayerId', 'mapId'))
            ->join(array('b' => 'playersingame'), 'a."gameId" = b."gameId"', null)
            ->where('"isOpen" = false')
            ->where('"isActive" = true')
            ->where('a."gameId" IN ?', $mPlayersInGame->getSelectForMyGames($playerId))
            ->where('b."playerId" = ?', $playerId)
            ->order('begin DESC');
        try {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
            $paginator->setCurrentPageNumber($pageNumber);
            $paginator->setItemCountPerPage(10);
        } catch (Exception $e) {
            $l = new Coret_Model_Logger('www');
            $l->log($select->__toString());
            $l->log($e);
            throw $e;
        }

        $mPlayer = new Application_Model_Player();

        foreach ($paginator as &$val) {
            $mPlayersInGame = new Application_Model_PlayersInGame($val['gameId']);
            $val['players'] = $mPlayersInGame->getGamePlayers();
            $val['playerTurn'] = $mPlayer->getPlayer($val['turnPlayerId']);
            $mMapPlayers = new Application_Model_MapPlayers($val['mapId']);
            $val['teams'] = $mMapPlayers->getMapPlayerIdToBackgroundColorRelations();
        }

        return $paginator;
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

    public function setNewGameMaster($gameMasterId)
    {
        if ($gameMasterId) {
            $data = array(
                'gameMasterId' => $gameMasterId
            );
            $this->updateGame($data);
        }
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

    public function updateGameMaster($playerId)
    {
        $select = $this->_db->select()
            ->from('playersingame', 'gameId')
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $this->getGameMasterId())
            ->where('"webSocketServerUserId" IS NOT NULL');

        if (!$this->selectOne($select)) {
            $data = array(
                'gameMasterId' => $playerId
            );
            $this->updateGame($data);
        }
    }

    public function isGameMaster($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('gameMasterId'))
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_gameId)
            ->where('"gameMasterId" = ?', $playerId);

        return $this->selectOne($select);
    }

    public function isGameStarted()
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where('"isOpen" = false')
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
        echo '$nextPlayerId=' . $nextPlayerId . ' $turnNumber=' . $turnNumber . "\n";
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

