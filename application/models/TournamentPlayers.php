<?php

class Application_Model_TournamentPlayers extends Coret_Db_Table_Abstract
{
    protected $_name = 'tournamentplayers';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function addPlayer($tournamentId, $playerId)
    {
        $data = array(
            'tournamentId' => $tournamentId,
            'playerId' => $playerId
        );

        return $this->_db->insert($this->_name, $data);
    }

    public function removePlayer($tournamentId, $playerId)
    {
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('tournamentId') . ' = ?', $tournamentId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId),
        );

        return $this->delete($where);
    }

    public function updateStage($stage, $tournamentId, $playerId)
    {
        $data = array(
            'stage' => $stage
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('tournamentId') . ' = ?', $tournamentId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId),
        );

        return $this->update($data, $where);
    }
}

