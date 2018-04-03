<?php

class Application_Model_MapRuins extends Coret_Db_Table_Abstract
{
    protected $_name = 'mapruins';
    protected $_primary = 'mapRuinId';
    protected $_sequence = 'mapruins_mapRuinId_seq';
    protected $_mapId;

    public function __construct($mapId, $db = null)
    {
        $this->_mapId = $mapId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getMapRuins()
    {
        $select = $this->_db->select()
            ->from($this->_name, array($this->_primary, 'x', 'y', 'ruinId'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId);

        $ret = array();

        foreach ($this->selectAll($select) as $val) {
            $ret[$val[$this->_primary]] = array(
                'x' => $val['x'],
                'y' => $val['y'],
                'ruinId' => $val['ruinId']
            );
        }

        return $ret;
    }

    public function add($x, $y, $ruinId)
    {
        $data = array(
            'mapId' => $this->_mapId,
            'x' => $x,
            'y' => $y,
            'ruinId' => $ruinId
        );
        return $this->insert($data);
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

