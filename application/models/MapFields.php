<?php

class Application_Model_MapFields extends Coret_Db_Table_Abstract
{
    protected $_name = 'mapfields';
    protected $_primary = 'mapId';
    protected $_sequence = "mapfields_mapFieldId_seq";
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

    public function getMapFields()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('x', 'y', 'type'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId)
            ->order(array('y', 'x'));

        $mapFields = array();

        foreach ($this->selectAll($select) as $val) {
            $mapFields[$val['y']][$val['x']] = $val['type'];
        }

        return $mapFields;
    }

    public function add($x, $y, $type)
    {
        $data = array(
            'mapId' => $this->_mapId,
            'x' => $x,
            'y' => $y,
            'type' => $type
        );

        $this->insert($data);
    }

    public function edit($x, $y, $type)
    {
        $data = array(
            'type' => $type
        );
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId),
            $this->_db->quoteInto('x = ?', $x),
            $this->_db->quoteInto('y = ?', $y)
        );
        $this->update($data, $where);
    }

    public function mirrorBottom()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('x', 'y', 'type'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->_mapId)
            ->order(array('y', 'x'));

        $mapFields = array();

        foreach ($this->selectAll($select) as $val) {
            $mapFields[$val['y']][$val['x']] = $val['type'];
        }

        $max = count($mapFields);

        $mirrorMapFields = array_reverse($mapFields);

        foreach ($mirrorMapFields as $y => $row) {
            $newY = $y + $max;
            foreach ($row[$y] as $x => $type) {
                $mapFields[$newY][$x] = $type;
            }
        }


    }
}

