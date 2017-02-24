<?php

class Application_Model_Terrain extends Coret_Db_Table_Abstract
{

    protected $_name = 'terrain';
    protected $_primary = 'terrainId';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getTerrainLang()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('flying', 'swimming', 'walking', 'type'))
            ->where('id_lang = ?', Zend_Registry::get('id_lang'))
            ->join($this->_name . '_Lang', $this->_name . ' . ' . $this->_db->quoteIdentifier($this->_primary) . ' = ' . $this->_db->quoteIdentifier($this->_name . '_Lang') . ' . ' . $this->_db->quoteIdentifier($this->_primary), 'name')
            ->order($this->_name . '.' . $this->_primary);

        return $this->selectAll($select);
    }

        public function getTerrain()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('flying', 'swimming', 'walking', 'type'))
            ->where('id_lang = ?', Zend_Registry::get('config')->id_lang)
            ->join($this->_name . '_Lang', $this->_name . ' . ' . $this->_db->quoteIdentifier($this->_primary) . ' = ' . $this->_db->quoteIdentifier($this->_name . '_Lang') . ' . ' . $this->_db->quoteIdentifier($this->_primary), 'name')
            ->order($this->_name . '.' . $this->_primary);

        return $this->selectAll($select);
    }
}

