<?php

class Application_Model_MapCastles extends Coret_Db_Table_Abstract
{
    protected $_name = 'mapcastles';
    protected $_primary = 'mapCastleId';
    protected $_sequence = 'mapcastles_mapCastleId_seq';
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

    public function getMapCastles()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('x', 'y', 'enclaveNumber', 'mapCastleId', 'name', 'income', 'capital', 'defense', 'sideId'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId);

        $castles = $this->selectAll($select);
        $mapCastles = array();

        foreach ($castles as $val) {
            $mapCastles[$val['mapCastleId']] = $val;
        }

        return $mapCastles;
    }

    public function getCastle($castleId)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('x', 'y', 'enclaveNumber', 'mapCastleId', 'name', 'income', 'capital', 'defense', 'sideId'))
            ->where($this->_db->quoteIdentifier('mapCastleId') . ' = ?', $castleId);

        return $this->selectRow($select);
    }

    public function getMapCastlesIds()
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId);

        $ids = '';

        foreach ($this->selectAll($select) as $row) {
            if ($ids) {
                $ids .= ',';
            }
            $ids .= $row['castleId'];
        }

        return $ids;
    }

    public function getDefaultStartPositions()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('x', 'y', 'sideId', 'mapCastleId'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId)
            ->where('capital = true')
            ->order('sideId');

        $startPositions = array();

        foreach ($this->selectAll($select) as $row) {
            $startPositions[$row['sideId']] = $row;
        }
        return $startPositions;
    }

    public function add($x, $y, $castle = null)
    {
        $data = array(
            'mapId' => $this->_mapId,
            'x' => $x,
            'y' => $y
        );

        if ($castle) {
            if (isset($castle['defense'])) {
                $data['defense'] = $castle['defense'];
            }
            if (isset($castle['capital'])) {
                $data['capital'] = $this->parseBool($castle['capital']);
            }
            if (isset($castle['name'])) {
                $data['name'] = $castle['name'];
            }
            if (isset($castle['income'])) {
                $data['income'] = $castle['income'];
            }
            if (isset($castle['enclaveNumber'])) {
                $data['enclaveNumber'] = $castle['enclaveNumber'];
            }
        }

        return $this->insert($data);
    }

    public function edit($data, $mapCastleId)
    {
        $data['capital'] = $this->parseBool($data['capital']);
        $where = $this->_db->quoteInto($this->_db->quoteIdentifier('mapCastleId') . ' = ?', $mapCastleId);
        return $this->update($data, $where);
    }

    public function remove($id)
    {
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId),
            $this->_db->quoteInto($this->_db->quoteIdentifier($this->_primary) . ' = ?', $id)
        );

        $this->delete($where);
    }
}

