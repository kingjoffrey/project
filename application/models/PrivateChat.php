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

    public function getChatHistory()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('date', 'message', 'recipientId'))
            ->join(array('b' => 'player'), 'a.' . $this->_db->quoteIdentifier('recipientId') . ' = b.' . $this->_db->quoteIdentifier('playerId'), array('firstName', 'lastName'))
            ->where('a.' . $this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId)
            ->where('read = false')
            ->order($this->_primary);
        return $this->selectAll($select);
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
