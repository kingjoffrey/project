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
        $primary = $this->_db->quoteIdentifier($this->_primary);

        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('menu'))
            ->where('id_lang = ?', Zend_Registry::get('id_lang'))
            ->join($this->_name . '_Lang', 'a.' . $primary . ' = ' . $this->_db->quoteIdentifier($this->_name . '_Lang') . ' . ' . $primary, array('content'))
            ->order('a.' . $this->_primary);

        return $this->selectAll($select);
    }
}

