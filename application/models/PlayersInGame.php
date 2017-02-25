<?php

class Application_Model_PlayersInGame extends Coret_Db_Table_Abstract
{
    protected $_name = 'playersingame';
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

    public function joinGame($playerId, $sideId, $teamId)
    {
        $data = array(
            'gameId' => $this->_gameId,
            'playerId' => $playerId,
            'sideId' => $sideId,
            'teamId' => $teamId
        );

        $this->insert($data);
    }

    public function getOtherComputerPlayerIdSelect()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'playerId')
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('sideId') . ' IS NOT NULL')
            ->where('computer = true');

        return $select;
    }

    public function getGoldForAllPlayers()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('playerId', 'gold'))
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);

        $array = array();

        foreach ($this->selectAll($select) as $row) {
            $array[$row['playerId']] = $row['gold'];
        }

        return $array;
    }

    public function updatePlayerGold($playerId, $gold)
    {
        $data['gold'] = $gold;
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
        );

        return $this->update($data, $where);
    }

    public function turnActivate($playerId)
    {
        $data = array(
            'turnActive' => 'true'
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
        );

        $this->update($data, $where);

        $data['turnActive'] = 'false';

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('turnActive') . ' = ?', true),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' != ?', $playerId)
        );

        $this->update($data, $where);
    }

    public function setPlayerLostGame($playerId)
    {
        $data['lost'] = 'true';

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
        );

        $this->update($data, $where);
    }

    public function getSelectForMyGames($playerId)
    {
        return $this->_db->select()
            ->from($this->_name, 'gameId')
            ->where($this->_db->quoteIdentifier('sideId') . ' is not null')
            ->where('lost = false')
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);
    }

    public function getGamePlayers()
    {
        $sideId = $this->_db->quoteIdentifier('sideId');
        $gameId = $this->_db->quoteIdentifier('gameId');
        $playerId = $this->_db->quoteIdentifier('playerId');

        $select = $this->_db->select()
            ->from(array('b' => $this->_name), array('playerId', 'teamId', 'turnActive', 'lost', 'gold'))
            ->join(array('a' => 'player'), 'a.' . $playerId . ' = b.' . $playerId, array('firstName', 'lastName', 'computer'))
            ->join(array('c' => 'side'), 'b . ' . $sideId . ' = c . ' . $sideId, array('color' => 'shortName', 'longName', 'backgroundColor', 'textColor', 'minimapColor'))
            ->where($gameId . ' = ?', $this->_gameId)
            ->where('b . ' . $sideId . ' is not null')
            ->order('b.sideId');

        $players = array();

        foreach ($this->selectAll($select) as $v) {
            $players[$v['playerId']] = $v;
        }

        return $players;
    }
}

