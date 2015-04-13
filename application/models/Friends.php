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

    public function getFriends($pageNumber, $playerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'friendId')
            ->join(array('b' => 'player'), 'a."friendId" = b."playerId"', array('firstName', 'lastName'))
            ->where('a.' . $this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setCurrentPageNumber($pageNumber);
        $paginator->setItemCountPerPage(20);

        return $paginator;
    }
}

