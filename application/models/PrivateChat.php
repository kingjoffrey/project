<?php

class Application_Model_PrivateChat extends Coret_Db_Table_Abstract
{

    protected $_name = 'privatechat';
    protected $_primary = 'chatId';

    private $_playerId;

    public function __construct($playerId, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_playerId = $playerId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getChatHistoryThreads($pageNumber)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('date', 'message', 'playerId', 'read'))
            ->join(array('b' => 'player'), 'a.' . $this->_db->quoteIdentifier('playerId') . ' = b.' . $this->_db->quoteIdentifier('playerId'), array('firstName', 'lastName'))
            ->where('a.' . $this->_db->quoteIdentifier('recipientId') . ' = ?', $this->_playerId)
            ->order($this->_primary . ' DESC');
echo $select->__toString();
        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setCurrentPageNumber($pageNumber);
        $paginator->setItemCountPerPage(20);

        return $paginator;
    }

    public function getChatHistoryMessages($playerId, $pageNumber)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('date', 'message', 'playerId', 'read', 'chatId'))
            ->join(array('b' => 'player'), 'a.' . $this->_db->quoteIdentifier('playerId') . ' = b.' . $this->_db->quoteIdentifier('playerId'), array('firstName', 'lastName'))
            ->where('a.' . $this->_db->quoteIdentifier('recipientId') . ' = ?', $this->_playerId)
            ->order($this->_primary . ' DESC');

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setCurrentPageNumber($pageNumber);
        $paginator->setItemCountPerPage(20);

        return $paginator;
    }

    public function getChatHistoryCount()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'count(*)')
            ->where($this->_db->quoteIdentifier('recipientId') . ' = ?', $this->_playerId)
            ->where('read = false');
        return $this->selectOne($select);
    }

    public function insertChatMessage($recipientId, $message, $read)
    {
        $data = array(
            'message' => $message,
            'recipientId' => $recipientId,
            'playerId' => $this->_playerId,
            'read' => $read
        );
        $this->insert($data);
    }

    public function readChatMessage($chatId, $read)
    {
        if ($read) {
            $data = array(
                'read' => true
            );
            $where = array(
                $this->_db->quoteInto($this->_db->quoteIdentifier($this->_primary) . ' = ?', $chatId)
            );
            $this->update($data, $where);
        }

        $select = $this->_db->select()
            ->from($this->_name, 'message')
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $chatId);

        return $this->selectOne($select);
    }
}
