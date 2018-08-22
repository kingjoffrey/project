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

    public function getThreads()
    {
        $select1 = $this->_db->select()
            ->from($this->_name, 'recipientId')
            ->where($this->_db->quoteIdentifier('recipientId') . '  = ? OR ' . $this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId);

        $select2 = $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where($this->_db->quoteIdentifier('recipientId') . '  = ? OR ' . $this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId);

        $select = $this->_db->select()
            ->union(array($select1, $select2));

        return $select;
    }

    public function getThreadUnreadMessageCount($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'count(message)')
            ->where(
                $this->_db->quoteInto($this->_db->quoteIdentifier('recipientId') . '  = ?', $playerId) .
                $this->_db->quoteInto(' AND ' . $this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId) .
                ' OR ' .
                $this->_db->quoteInto($this->_db->quoteIdentifier('recipientId') . '  = ?', $this->_playerId) .
                $this->_db->quoteInto(' AND ' . $this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
            );

        echo $select->__toString();
    }

    public function getChatHistoryMessages($playerId)
    {
        $data = array(
            'read' => true
        );
        $where = array(
            'read = false',
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('recipientId') . ' = ?', $this->_playerId)
        );
        $this->update($data, $where);

        $select1 = $this->_db->select()
            ->from($this->_name, 'chatId')
            ->where($this->_db->quoteIdentifier('recipientId') . ' = ?', $this->_playerId)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);

        $select2 = $this->_db->select()
            ->from($this->_name, 'chatId')
            ->where($this->_db->quoteIdentifier('recipientId') . ' = ?', $playerId)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId);

        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('date', 'message', 'playerId', 'read', 'chatId'))
            ->join(array('b' => 'player'), 'a.' . $this->_db->quoteIdentifier('playerId') . ' = b.' . $this->_db->quoteIdentifier('playerId'), array('firstName', 'lastName'))
            ->where($this->_db->quoteIdentifier($this->_primary) . ' IN (?)', new Zend_Db_Expr($select1))
            ->orWhere($this->_db->quoteIdentifier($this->_primary) . ' IN (?)', new Zend_Db_Expr($select2))
            ->order($this->_primary . ' DESC')
            ->limit(10);

        return $this->selectAll($select);
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
}
