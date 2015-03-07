<?php

class Application_Model_TowersInGame extends Coret_Db_Table_Abstract
{
    protected $_name = 'towersingame';
    protected $_primary = 'towerId';
    protected $_db;

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getTowers()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array($this->_primary, 'playerId'))
            ->where('a.' . $this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);

        $result = $this->selectAll($select);

        $towers = array();

        foreach ($result as $row) {
            $towers[$row['towerId']] = $row['playerId'];
        }

        return $towers;
    }

    public function changeTowerOwner($towerId, $playerId)
    {
        $data = array(
            'playerId' => $playerId
        );
        $where = array(
            $this->_db->quoteInto('"towerId" = ?', $towerId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
        );

        $this->setQuiet(true);
        return $this->update($data, $where);
    }

    public function addTower($towerId, $playerId)
    {
        $data = array(
            'towerId' => $towerId,
            'gameId' => $this->_gameId,
            'playerId' => $playerId
        );

        return $this->insert($data);
    }
}

