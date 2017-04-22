<?php

class Application_Model_Tournament extends Coret_Db_Table_Abstract
{
    protected $_name = 'tournament';
    protected $_primary = 'tournamentId';
    protected $_sequence = 'tournament_tournamentId_seq';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getList()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('tournamentId', 'start', 'limit'))
            ->order('start');

        return $this->selectAll($select);
    }

    public function getCurrent()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'tournamentId')
            ->where($this->_db->quoteIdentifier('end') . ' IS NULL');

        return $this->selectOne($select);
    }

    public function getLimit()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'limit')
            ->where($this->_db->quoteIdentifier('end') . ' IS NULL');

        return $this->selectOne($select);
    }
}

