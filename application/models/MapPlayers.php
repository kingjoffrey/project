<?php

class Application_Model_MapPlayers extends Coret_Db_Table_Abstract
{
    protected $_name = 'mapplayers';
    protected $_primary = 'mapPlayerId';
    protected $_sequence = '';
    protected $mapId;

    public function __construct($mapId, $db = null)
    {
        $this->mapId = $mapId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getNumberOfPlayersForNewGame()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'count(*)')
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId);

        return $this->selectOne($select);
    }

    public function getMapPlayerIds()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'mapPlayerId')
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->order('startOrder');

        $array = array();

        foreach ($this->selectAll($select) as $row) {
            $array[] = $row['mapPlayerId'];
        }

        return $array;
    }

    public function getShortNameToMapPlayerIdRelations()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('mapPlayerId', 'shortName'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId);

        $array = array();

        foreach ($this->selectAll($select) as $row) {
            $array[$row['shortName']] = $row['mapPlayerId'];
        }

        return $array;
    }

    public function getMapPlayerIdToBackgroundColorRelations()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('mapPlayerId', 'backgroundColor'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->order('startOrder');

        $array = array();

        foreach ($this->selectAll($select) as $row) {
            $array[$row['mapPlayerId']] = $row['backgroundColor'];
        }

        return $array;
    }

    public function getLongNames()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('mapPlayerId', 'longName'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->order('startOrder');

        $array = array();

        foreach ($this->selectAll($select) as $row) {
            $array[$row['mapPlayerId']] = $row['longName'];
        }

        return $array;
    }

    public function getAll()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('backgroundColor', 'castleId', 'longName', 'mapPlayerId', 'minimapColor', 'shortName', 'textColor'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->order('startOrder');

        $array = array();

        foreach ($this->selectAll($select) as $row) {
            $array[$row['mapPlayerId']] = $row;
        }

        return $array;
    }

    public function getFirstMapPlayerId()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'mapPlayerId')
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->order('startOrder');

        return $this->selectOne($select);
    }

    public function getCapitals()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('castleId', 'shortName'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->order('startOrder');

        $array = array();

        foreach ($this->selectAll($select) as $row) {
            $array[$row['shortName']] = $row['castleId'];
        }

        return $array;
    }

    public function getColorByMapPlayerId($mapPlayerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'shortName')
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' = ?', $mapPlayerId);

        return $this->selectOne($select);
    }
}

