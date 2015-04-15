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
            ->from($this->_name, array('date', 'message', 'recipientId'))
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId)
            ->order($this->_primary);
        return $this->selectAll($select);
    }

    public function insertChatMessage($recipientId, $message)
    {
        $data = array(
            'message' => $message,
            'recipientId' => $recipientId,
            'playerId' => $this->_playerId
        );
        $this->insert($data);
    }
}
