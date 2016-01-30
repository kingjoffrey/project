<?php

class Application_Model_MapPlayers extends Coret_Db_Table_Abstract
{
    protected $_name = 'mapplayers';
    protected $_primary = 'mapPlayerId';
    protected $_sequence = '';
    protected $_mapId;

    public function __construct($mapId, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_mapId = $mapId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function create($maxPlayers)
    {
        for ($i = 1; $i <= $maxPlayers; $i++) {

        }
    }

    public function getNumberOfPlayersForNewGame()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'count(*)')
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId);

        return $this->selectOne($select);
    }

    public function getMapPlayerIds()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'mapPlayerId')
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId)
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
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId);

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
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId)
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
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId)
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
            ->from($this->_name, array('backgroundColor', 'capitalId', 'longName', 'mapPlayerId', 'minimapColor', 'shortName', 'textColor'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId)
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
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId)
            ->order('startOrder');

        return $this->selectOne($select);
    }

    public function getCapitals()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('castleId', 'shortName'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId)
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
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' = ?', $mapPlayerId);

        return $this->selectOne($select);
    }
}

