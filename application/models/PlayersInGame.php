<?php

class Application_Model_PlayersInGame extends Coret_Db_Table_Abstract
{
    protected $_name = 'playersingame';
//    protected $_primary = 'mapPlayerId';
    protected $_sequence = '';
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

    public function getAll()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'playerId')
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', 'computer')
            ->join(array('c' => 'mapplayers'), 'a . "mapPlayerId" = c . "mapPlayerId"', array('mapPlayerId', 'castleId'))
            ->where('a . ' . $this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where('a . ' . $this->_db->quoteIdentifier('mapPlayerId') . ' IS NOT NULL');

        $players = array();

        foreach ($this->selectAll($select) as $row) {
            $players[$row['mapPlayerId']] = $row;
        }

        return $players;
    }

    public function getAllColors()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'playerId')
            ->join(array('b' => 'mapplayers'), 'a . "mapPlayerId" = b . "mapPlayerId"', array('color' => 'shortName'))
            ->where('a . ' . $this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where('a . ' . $this->_db->quoteIdentifier('mapPlayerId') . ' IS NOT NULL')
            ->order('startOrder');

        $colors = array();

        foreach ($this->selectAll($select) as $row) {
            $colors[$row['playerId']] = $row['color'];
        }

        return $colors;
    }

    public function getPlayerIdByMapPlayerId($mapPlayerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'playerId')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' = ?', $mapPlayerId);

        return $this->selectOne($select);
    }

    public function getColorByPlayerId($playerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), null)
            ->join(array('b' => 'mapplayers'), 'a . "mapPlayerId" = b . "mapPlayerId"', array('color' => 'shortName'))
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);

        return $this->selectOne($select);
    }

    public function getMapPlayerIdByPlayerId($playerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'mapPlayerId')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);

        return $this->selectOne($select);
    }

    public function disconnectNotActive()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'playerId')
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
            ->where('a . ' . $this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('webSocketServerUserId') . ' IS NULL')
            ->where('computer = false');

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' IN (?)', new Zend_Db_Expr($select->__toString())),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
        );

        $this->delete($where);
    }

    public function joinGame($playerId, $mapPlayerId)
    {
        $data = array(
            'gameId' => $this->_gameId,
            'playerId' => $playerId,
            'mapPlayerId'=>$mapPlayerId
        );

        $this->insert($data);
    }

    public function isPlayerInGame($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'gameId')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);

        return $this->selectOne($select);
    }

    public function getComputerPlayerId()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'min(b."playerId")')
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
            ->where($this->_db->quoteIdentifier('gameId') . ' != ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' IS NOT NULL')
            ->where('computer = true');

        $ids = $this->getComputerPlayersIds();
        if ($ids) {
            $select->where('a."playerId" NOT IN (?)', new Zend_Db_Expr($ids));
        }

        return $this->selectOne($select);
    }

    private function getComputerPlayersIds()
    {
        $ids = '';
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'playerId')
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' IS NOT NULL')
            ->where('computer = true');

        foreach ($this->selectAll($select) as $row) {
            if ($ids) {
                $ids .= ',';
            }
            $ids .= $row['playerId'];
        }

        return $ids;

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

    public function checkAccessKey($playerId, $accessKey)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('accessKey') . ' = ?', $accessKey);

        return $this->selectOne($select);
    }

    public function updateWSSUId($playerId, $wssuid)
    {
        $data = array(
            'webSocketServerUserId' => $wssuid
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
        );

        $this->update($data, $where);
    }

    public function setTeam($playerId, $teamId)
    {
        $data = array(
            'team' => $teamId
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
        );

        $this->update($data, $where);
    }

    public function getSelectForMyGames($playerId)
    {
        return $this->_db->select()
            ->from($this->_name, 'gameId')
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' is not null')
            ->where('lost = false')
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);
    }

    public function getGamePlayers()
    {
        $select = $this->_db->select()
            ->from(array('b' => $this->_name), array('playerId', 'team', 'turnActive', 'lost', 'gold'))
            ->join(array('a' => 'player'), 'a."playerId" = b."playerId"', array('firstName', 'lastName', 'computer'))
            ->join(array('c' => 'mapplayers'), 'b . "mapPlayerId" = c . "mapPlayerId"', array('color' => 'shortName', 'longName', 'backgroundColor', 'textColor', 'minimapColor'))
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where('b . ' . $this->_db->quoteIdentifier('mapPlayerId') . ' is not null');

        $players = array();

        foreach ($this->selectAll($select) as $v) {
            $players[$v['playerId']] = $v;
        }

        return $players;
    }

    public function getAccessKey($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'accessKey')
            ->where('"playerId" = ?', $playerId)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);

        return $this->_db->fetchOne($select);
    }
}

