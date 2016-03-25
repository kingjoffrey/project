<?php

class Application_Model_Help extends Coret_Db_Table_Abstract
{
    protected $_name = 'help';
    protected $_primary = 'helpId';
    protected $_sequence = 'help_helpId_seq';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function get()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('action', 'title', 'content'));

        return $this->selectAll($select);
    }
}

