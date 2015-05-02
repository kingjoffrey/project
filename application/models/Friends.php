<?php

class Application_Model_Friends extends Coret_Db_Table_Abstract
{
    protected $_name = 'friends';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function create($playerId, $friendId)
    {
        $this->insert(array('playerId' => $playerId, 'friendId' => $friendId));
    }

    public function remove($playerId, $friendId)
    {
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('friendId') . ' = ?', $friendId)
        );
        $this->delete($where);
    }

    public function getFriends($playerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'friendId')
            ->join(array('b' => 'player'), 'a."friendId" = b."playerId"', array('firstName', 'lastName'))
            ->where('a.' . $this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);

        return $this->selectAll($select);
    }

    public function getFriendsIds($playerId)
    {
        $select1 = $this->_db->select()
            ->from(array('a' => $this->_name), 'friendId')
            ->where('a.' . $this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);

        $select2 = $this->_db->select()
            ->from(array('a' => $this->_name), array('friendId' => 'playerId'))
            ->where('a.' . $this->_db->quoteIdentifier('friendId') . ' = ?', $playerId);

        return array_merge($this->selectAll($select1), $this->selectAll($select2));
    }
}

