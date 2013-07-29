<?php

class Application_Model_MapFields extends Game_Db_Table_Abstract
{
    protected $_name = 'mapfields';
    protected $_primary = 'mapId';
    protected $_sequence = "map_mapId_seq";
    protected $_db;
    protected $mapId;

    public function __construct($mapId)
    {
        $this->mapId = $mapId;
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function getMapFields()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId);
        try {
            $all = $this->_db->query($select)->fetchAll();
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }

        $mapFields = array();

        foreach ($all as $val) {
            $mapFields[$val['y']][$val['x']] = $val['type'];
        }

        return $mapFields;
    }

}

