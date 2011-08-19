<?php

class Application_Model_Player extends Warlords_Db_Table_Abstract
{
    protected $_name = 'player';
    protected $_primary = 'playerId';
    protected $_sequence = 'player_playerId_seq';
    protected $_db;
    protected $_id;
    protected $_fbid;
    protected $_activity;
    protected $_columns = array();

    public function __construct($fbId, $playerId = 0) {

        $this->_fbid = $fbId;
        $this->_id = $playerId;
        $this->_db = $this->getDefaultAdapter();

        parent::__construct();
    }

    public function noPlayer() {
        $select = $this->_db->select()
             ->from($this->_name, $this->_primary)
             ->where('"fbId" = ?', $this->_fbid);
        $result = $this->_db->query($select)->fetchAll();
        if(empty($result[0][$this->_primary]))
            return true;
    }

    public function createPlayer() {
        $dane = array(
            'fbId'=> $this->_fbid,
            'activity'=>'2011-06-15'
        );
        $this->_db->insert($this->_name, $dane);
        $seq = $this->_db->quoteIdentifier( $this->_sequence );
        return $this->_db->lastSequenceId($seq);
    }

    public function getPlayer() {
        $select = $this->_db->select()
             ->from($this->_name)
             ->where('"fbId" = ?', $this->_fbid);
        $result = $this->_db->query($select)->fetchAll();
        if(isset($result[0])) return $result[0];
    }
    
    public function updatePlayer($data) {
        $where = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        return $this->_db->update($this->_name, $data, $where);
    }
}

