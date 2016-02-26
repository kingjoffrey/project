<?php

class Application_Model_Side extends Coret_Db_Table_Abstract
{
    protected $_name = 'side';
    protected $_primary = 'sideId';
    protected $_sequence = "side_sideId_seq";

    protected $_sideId;

    public function __construct($sideId = 0, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->mapId = $sideId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getWithLimit($limit)
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->limit($limit);

        return $this->selectAll($select);
    }

    public function getAll()
    {
        $select = $this->_db->select()
            ->from($this->_name);

        return $this->selectAll($select);
    }
}

