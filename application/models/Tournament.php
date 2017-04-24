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
        $mapIden = $this->_db->quoteIdentifier('mapId');

        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('tournamentId', 'start', 'limit', 'finished'))
            ->join(array('b' => 'map'), 'a.' . $mapIden . ' = b.' . $mapIden, 'name')
            ->order('start DESC');

        return $this->selectAll($select);
    }

    public function getCurrent()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('mapId', 'tournamentId'))
            ->where($this->_db->quoteIdentifier('finished') . ' = false');

        return $this->selectRow($select);
    }

    public function checkLimit($tournamentId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'limit')
            ->where($this->_db->quoteIdentifier('tournamentId') . ' = ?', $tournamentId)
            ->where($this->_db->quoteIdentifier('finished') . ' = false');

        return $this->selectOne($select);
    }

    public function getFee($tournamentId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'fee')
            ->where($this->_db->quoteIdentifier('tournamentId') . ' = ?', $tournamentId);

        return $this->selectOne($select);
    }

    public function end($tournamentId)
    {
        $data = array(
            'finished' => 'true'
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('tournamentId') . ' = ?', $tournamentId)
        );

        return $this->update($data, $where);
    }
}

