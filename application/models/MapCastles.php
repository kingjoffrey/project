<?php

class Application_Model_MapCastles extends Coret_Db_Table_Abstract
{
    protected $_name = 'mapcastles';
    protected $_primary = 'mapCastleId';
    protected $_sequence = 'mapcastles_mapCastleId_seq';
    protected $mapId;

    public function __construct($mapId, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->mapId = $mapId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getMapCastles()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('x', 'y', 'enclaveNumber'))
            ->join(array('b' => 'castle'), 'a."castleId"=b."castleId"', array('castleId', 'name', 'income', 'capital', 'defense'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId);

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
            ->from($this->_name, 'castleId')
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId);

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
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->where('capital = true')
            ->order('mapCastleId');

        $startPositions = array();

        foreach ($this->selectAll($select) as $position) {
            $startPositions[$position['castleId']] = $position;
        }

        return $startPositions;
    }

    public function add($x, $y, $castleId)
    {
        $data = array(
            'mapId' => $this->mapId,
            'castleId' => $castleId,
            'x' => $x,
            'y' => $y
        );

        $this->insert($data);
    }

    public function remove($x, $y)
    {
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId),
            $this->_db->quoteInto('x = ?', $x),
            $this->_db->quoteInto('y = ?', $y),
        );

        $this->delete($where);
    }

}

