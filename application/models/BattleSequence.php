<?php

class Application_Model_BattleSequence extends Coret_Db_Table_Abstract
{
    protected $_name = '';
    protected $_primary = '';
    protected $_gameId;

    public function __construct($gameId, $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function add($playerId, $unitId, $sequence)
    {
        $data = array(
            'gameId' => $this->_gameId,
            'playerId' => $playerId,
            'unitId' => $unitId,
            'sequence' => $sequence
        );
        $this->insert($data);
    }

    public function get($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('unitId', 'sequence'))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId);

        return $this->selectAll($select);
    }
}

