<?php

class Application_Model_Websocket extends Coret_Db_Table_Abstract
{
    protected $_name = 'websocket';
    protected $_primary = 'websocketId';
    protected $_sequence = 'websocket_websocketId_seq';

    protected $_playerId;

    public function __construct($playerId, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }

        $this->_playerId = $playerId;
    }

//    public function auth($accessKey, $websocketId)
//    {
//        $select = $this->_db->select()
//            ->from($this->_name, 'playerId')
//            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId)
//            ->where($this->_db->quoteIdentifier('websocketId') . ' = ?', $websocketId)
//            ->where($this->_db->quoteIdentifier('accessKey') . ' = ?', $accessKey);
//
//        return $this->selectOne($select);
//    }

    public function generateKey()
    {
        return md5(rand(0, time()));
    }

    public function create($accessKey)
    {
        $data = array(
            'playerId' => $this->_playerId,
            'accessKey' => $accessKey,
        );

        $this->insert($data);
    }

    public function checkAccessKey($accessKey)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId)
            ->where($this->_db->quoteIdentifier('accessKey') . ' = ?', $accessKey);

        return $this->selectOne($select);
    }

    public function disconnect($accessKey)
    {
        $data = array(
            'active' => 'false'
        );
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('accessKey') . ' = ?', $accessKey)
        );
        $this->update($data, $where);
    }
}

