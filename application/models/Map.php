<?php

class Application_Model_Map extends Coret_Db_Table_Abstract
{
    protected $_name = 'map';
    protected $_primary = 'mapId';
    protected $_sequence = "map_mapId_seq";
    protected $_mapId;

    public function __construct($mapId = 0, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_mapId = $mapId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function create($params, $playerId)
    {
        $data = array(
            'name' => $params['name'],
            'maxPlayers' => $params['maxPlayers'],
            'playerId' => $playerId
        );

        $this->_db->insert($this->_name, $data);

        return $this->_db->lastSequenceId($this->_db->quoteIdentifier($this->_sequence));
    }

    public function getPlayerMapList($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('date', 'mapId', 'maxPlayers', 'name'))
            ->where('publish = false')
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);
        return $this->selectAll($select);
    }

    public function get()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('date', 'mapId', 'maxPlayers', 'name'))
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_mapId);

        return $this->selectRow($select);
    }

    public function getName()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'name')
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_mapId);

        return $this->selectOne($select);
    }

    public function getMaxPlayers()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'maxPlayers')
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_mapId);

        return $this->selectOne($select);
    }

    public function getAllMapsList()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('mapId', 'name'))
            ->order('mapId');

        $maps = array();

        foreach ($this->selectAll($select) as $map) {
            $maps[$map['mapId']] = $map['name'];
        }

        return $maps;
    }

    public function getAllMultiMapsList()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('mapId', 'name'))
            ->where('tutorial = false')
            ->where('publish = true')
            ->order('mapId')
            ->limit(1);

        $list = $this->selectAll($select);

        $maps = array();

        foreach ($list as $map) {
            $maps[$map['mapId']] = $map['name'];
        }

        rsort($maps);

        return $maps;
    }

    public function getTestMap($mapId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'name')
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $mapId);

        $list = $this->selectAll($select);

        return array(
            $mapId => $list[0]['name']
        );
    }

    public function getMinMapId()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'min("mapId")');

        return $this->selectOne($select);
    }

    public function publish()
    {
        $data = array('publish' => true);
        $where = array($this->_db->quoteInto($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_mapId));
        return $this->update($data, $where);
    }

    public function deleteNotPublished($playerId)
    {
        $where = array(
            $this->_db->quoteInto('publish = ?', $this->parseBool(false)),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId),
            $this->_db->quoteInto($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_mapId)
        );
        return $this->delete($where);
    }

    public function changeNameAndMaxPlayers($data, $playerId)
    {
        $data = array(
            'name' => $data['name'],
            'maxPlayers' => $data['max']
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId),
            $this->_db->quoteInto($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_mapId)
        );

        return $this->update($data, $where);
    }
}

