<?php

class Application_Model_Tutorial extends Coret_Db_Table_Abstract
{
    protected $_name = 'tutorial';
    protected $_primary = 'tutorialId';
    protected $_playerId;

    public function __construct($playerId, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_playerId = $playerId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function get()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId);

        return $this->selectRow($select);
    }
}

