<?php

class Application_Model_Player extends Coret_Db_Table_Abstract
{

    protected $_name = 'player';
    protected $_primary = 'playerId';
    protected $_sequence = 'player_playerId_seq';
    protected $_playerId;

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
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

    public function hallOfFame()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('playerId', 'firstName', 'lastName', 'score'))
            ->where('computer = false')
            ->where('score > 0')
            ->where($this->_db->quoteIdentifier('playerId') . ' > 0')
            ->order('score desc')
            ->limit(100);

        return $this->selectAll($select);
    }

    public function isComputer($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'computer')
            ->where('"playerId" = ?', $playerId);

        return $this->selectOne($select);
    }

    public function search($search)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('firstName', 'lastName', 'playerId'))
            ->where('computer = false')
            ->where($this->_db->quoteIdentifier('playerId') . ' > 0')
            ->where($this->_db->quoteInto($this->_db->quoteIdentifier('firstName') . ' ~* ?', $search) . ' OR ' . $this->_db->quoteInto($this->_db->quoteIdentifier('firstName') . ' || \' \' || ' . $this->_db->quoteIdentifier('lastName') . ' ~* ?', $search))
            ->order('firstName desc');

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setCurrentPageNumber(1);
        $paginator->setItemCountPerPage(20);

        return $paginator;
    }

    public function getPlayersNames($in, $a = false)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('firstName', 'lastName', 'playerId'))
            ->where($this->_db->quoteIdentifier('playerId') . ' IN (?)', new Zend_Db_Expr($in))
            ->order(array('firstName', 'lastName'));

        $array = array();

        foreach ($this->selectAll($select) as $row) {
            $array[$row['playerId']] = $row['firstName'] . ' ' . $row['lastName'];
        }

        return $array;
    }

    public function getPlayers()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'playerId');

        return $this->selectAll($select);
    }

    public function getComputerPlayerId($subSelect)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'min("playerId")')
            ->where('computer = true')
            ->where('"playerId" NOT IN (?)', new Zend_Db_Expr($subSelect->__toString()));

        return $this->selectOne($select);
    }
}

