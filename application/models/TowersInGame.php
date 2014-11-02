<?php

class Application_Model_TowersInGame extends Coret_Db_Table_Abstract
{
    protected $_name = 'towersingame';
    protected $_primary = 'towerId';
    protected $_db;

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getTowers()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), $this->_primary)
            ->join(array('b' => 'playersingame'), 'a."playerId" = b."playerId" AND a."gameId" = b."gameId"')
            ->join(array('c' => 'mapplayers'), 'b . "mapPlayerId" = c . "mapPlayerId"', array('color' => 'shortName'))
            ->where('a.' . $this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);

        $result = $this->selectAll($select);

        $towers = array();

        foreach ($result as $row) {
            $towers[$row['towerId']] = $row['color'];
        }

        return $towers;
    }

    public function getPlayerTowers($playerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), $this->_primary)
            ->where('"playerId" = ?', $playerId)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);

        return $this->selectAll($select);
    }

    public function getTower($towerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), $this->_primary)
            ->join(array('b' => 'playersingame'), 'a."playerId" = b."playerId" AND a."gameId" = b."gameId"')
            ->join(array('c' => 'mapplayers'), 'b . "mapPlayerId" = c . "mapPlayerId"', array('color' => 'shortName'))
            ->where('"' . $this->_primary . '" = ?', $towerId)
            ->where('a.' . $this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function getTowerOwnerId($towerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'playerId')
            ->where('"' . $this->_primary . '" = ?', $towerId)
            ->where('a.' . $this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function calculateIncomeFromTowers($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'towerId')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId);

        $towers = $this->selectAll($select);

        return count($towers) * 5;
    }

    public function towerExists($towerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'towerId')
            ->where('"towerId" = ?', $towerId)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function changeTowerOwner($towerId, $playerId)
    {
        $data = array(
            'playerId' => $playerId
        );
        $where = array(
            $this->_db->quoteInto('"towerId" = ?', $towerId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
        );

        $this->setQuiet(true);
        return $this->update($data, $where);
    }

    public function addTower($towerId, $playerId)
    {
        $data = array(
            'towerId' => $towerId,
            'gameId' => $this->_gameId,
            'playerId' => $playerId
        );

        return $this->insert($data);
    }
}

