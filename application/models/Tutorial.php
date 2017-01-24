<?php

class Application_Model_Tutorial extends Coret_Db_Table_Abstract
{
    protected $_name = 'tutorial';
    protected $_primary = 'tutorialId';
    protected $_sequence = 'tutorial_tutorialId_seq1';

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
            ->from($this->_name, null)
            ->where('id_lang = ?', Zend_Registry::get('id_lang'))
            ->join($this->_name . '_Lang', $this->_name . ' . ' . $this->_db->quoteIdentifier($this->_primary) . ' = ' . $this->_db->quoteIdentifier($this->_name . '_Lang') . ' . ' . $this->_db->quoteIdentifier($this->_primary), array('goal', 'description'))
            ->order('number, step');

        return $this->selectAll($select);
    }
}

