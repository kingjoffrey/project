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
        $primary = $this->_db->quoteIdentifier($this->_primary);
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('number', 'step'))
            ->where('id_lang = ?', Zend_Registry::get('id_lang'))
            ->join(array('b' => $this->_name . '_Lang'), 'a.' . $primary . ' = b.' . $primary, array('goal', 'description'))
            ->order('number')
            ->order('step');

        return $this->selectAll($select);
    }
}

