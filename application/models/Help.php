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
            ->from($this->_name, array('menu'))
            ->where('id_lang = ?', Zend_Registry::get('id_lang'))
            ->join($this->_name . '_Lang', $this->_name . ' . ' . $this->_db->quoteIdentifier($this->_primary) . ' = ' . $this->_db->quoteIdentifier($this->_name . '_Lang') . ' . ' . $this->_db->quoteIdentifier($this->_primary), array('content'))
            ->order($this->_name . '.' . $this->_primary);

        return $this->selectAll($select);
    }
}

