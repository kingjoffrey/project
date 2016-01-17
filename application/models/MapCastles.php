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
            ->from($this->_name, array('x', 'y', 'enclaveNumber', 'mapCastleId', 'name', 'income', 'capital', 'defense'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId);

        $castles = $this->selectAll($select);
        $mapCastles = array();

        foreach ($castles as $val) {
            $mapCastles[$val['castleId']] = $val;
        }

        return $mapCastles;
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
            ->from(array('a' => $this->_name), array('x', 'y'))
            ->join(array('b' => 'castle'), 'a."castleId"=b."castleId"', array('castleId'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId)
            ->where('capital = true')
            ->order('mapCastleId');

        $startPositions = array();

        foreach ($this->selectAll($select) as $position) {
            $startPositions[$position['castleId']] = $position;
        }

        return $startPositions;
    }

    public function add($x, $y)
    {
        $data = array(
            'mapId' => $this->_mapId,
            'x' => $x,
            'y' => $y
        );

        return $this->insert($data);
    }

    public function remove($x, $y)
    {
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId),
            $this->_db->quoteInto('x = ?', $x),
            $this->_db->quoteInto('y = ?', $y),
        );

        $this->delete($where);
    }

}

