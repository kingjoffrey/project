<?php

class Application_Model_Player extends Coret_Db_Table_Abstract
{

    protected $_name = 'player';
    protected $_primary = 'playerId';
    protected $_sequence = 'player_playerId_seq';
    protected $_playerId;

    public function __construct($db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function hasFacebookId($fbid)
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where('"fbId" = ?', $fbid);

        return $this->selectOne($select);
    }

    public function createPlayer($data)
    {
        $this->insert($data);

        return $this->_db->lastSequenceId($this->_db->quoteIdentifier($this->_sequence));
    }

    public function createComputerPlayer()
    {
        $data = array(
            'firstName' => 'Computer',
            'lastName' => 'Player',
            'computer' => 'true',
            'adminId' => 1
        );
        return $this->createPlayer($data);
    }

    public function getPlayer($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where('"' . $this->_primary . '" = ?', $playerId);

        return $this->selectRow($select);
    }

    public function updatePlayer($data, $playerId)
    {
        $where = $this->_db->quoteInto('"playerId" = ?', $playerId);

        return $this->update($data, $where);
    }

    public function addScore($playerId, $score)
    {
        $data = array(
            'score' => new Zend_Db_Expr('score + ' . $score)
        );

        $where = $this->_db->quoteInto($this->_db->quoteIdentifier($this->_primary) . ' = ?', $playerId);

        $this->update($data, $where);
    }

    public function hallOfFame($pageNumber)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('firstName', 'lastName', 'score'))
            ->where('computer = false')
            ->where('score > 0')
            ->where('"playerId" > 0')
            ->order('score desc');

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setCurrentPageNumber($pageNumber);
        $paginator->setItemCountPerPage(20);

        return $paginator;
    }

    public function isComputer($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'computer')
            ->where('"playerId" = ?', $playerId);

        return $this->selectOne($select);
    }


}

