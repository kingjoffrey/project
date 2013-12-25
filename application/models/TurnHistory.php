<?php

class Application_Model_TurnHistory extends Coret_Db_Table_Abstract
{

    protected $_name = 'turn';
    protected $_primary = 'turnId';

    public function __construct($gameId, $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getCurrentStatus()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('number', 'date'))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('date = (?)', new Zend_Db_Expr($this->_db->select()->from($this->_name, 'max(date)')->where('"gameId" = ?', $this->_gameId)));
        return $this->selectRow($select);
    }

    public function getTurnHistory()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('number', 'date'))
            ->join(array('b' => 'playersingame'), 'a."playerId" = b."playerId"', null)
            ->join(array('c' => 'mapplayers'), 'b."mapPlayerId" = c."mapPlayerId"', array('shortName'))
            ->where('a."gameId" = ?', $this->_gameId)
            ->where('b."gameId" = ?', $this->_gameId)
            ->order($this->_primary);
        return $this->selectAll($select);
    }

    public function add($playerId, $number)
    {
        $date = date('Y-m-d H:i:s');

        $data = array(
            'number' => $number,
            'date' => $date,
            'playerId' => $playerId,
            'gameId' => $this->_gameId
        );

        $this->insert($data);

        return $date;
    }
}
