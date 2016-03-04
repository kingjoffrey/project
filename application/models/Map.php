<?php

class Application_Model_Map extends Coret_Db_Table_Abstract
{
    protected $_name = 'map';
    protected $_primary = 'mapId';
    protected $_sequence = "map_mapId_seq";
    protected $mapId;

    public function __construct($mapId = 0, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->mapId = $mapId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function createMap($params, $playerId)
    {
        $data = array(
            'name' => $params['name'],
            'mapWidth' => $params['mapSize'],
            'mapHeight' => $params['mapSize'],
            'maxPlayers' => $params['maxPlayers'],
            'playerId' => $playerId
        );

        $this->_db->insert($this->_name, $data);

        return $this->_db->lastSequenceId($this->_db->quoteIdentifier($this->_sequence));
    }

    public function getPlayerMapList($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where('"playerId" = ?', $playerId);
        try {
            return $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getMap()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('mapWidth', 'mapHeight', 'name', 'mapId'))
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->mapId);

        return $this->selectRow($select);
    }

    public function getMapName()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'name')
            ->where('"' . $this->_primary . '" = ?', $this->mapId);
        try {
            return $this->_db->fetchOne($select);
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getAllMapsList()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->order('mapId');

        $list = $this->selectAll($select);

        $maps = array();

        foreach ($list as $map) {
            $maps[$map['mapId']] = $map['name'];
        }

        return $maps;
    }

    public function getAllMultiMapsList()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where('tutorial = false')
            ->order('mapId');

        $list = $this->selectAll($select);

        $maps = array();

        foreach ($list as $map) {
            $maps[$map['mapId']] = $map['name'];
        }

        return $maps;
    }

    public function getMinMapId()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'min("mapId")');

        return $this->selectOne($select);
    }
}

