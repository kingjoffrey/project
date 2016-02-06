<?php

class Application_Model_Terrain extends Coret_Db_Table_Abstract
{

    protected $_name = 'terrain';
    protected $_primary = 'terrainId';
    protected $mapId;

    public function __construct($db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getTerrain()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('flying', 'swimming', 'walking', 'type'))
            ->where('id_lang = ?', Zend_Registry::get('id_lang'))
            ->join($this->_name . '_Lang', $this->_name . ' . ' . $this->_db->quoteIdentifier($this->_primary) . ' = ' . $this->_db->quoteIdentifier($this->_name . '_Lang') . ' . ' . $this->_db->quoteIdentifier($this->_primary), 'name')
            ->order($this->_name . '.' . $this->_primary);

        return $this->selectAll($select);
    }

}
