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

    public function checkPlayer($tournamentId, $playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where($this->_db->quoteIdentifier('tournamentId') . ' = ?', $tournamentId)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
            ->where('stage = 1');

        return $this->selectOne($select);
    }

    public function getPlayersSelect($tournamentId, $stage)
    {
        return $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where($this->_db->quoteIdentifier('tournamentId') . ' = ?', $tournamentId)
            ->where('stage = ?', $stage)
            ->distinct('playerId');
    }

    public function getPlayers($tournamentId, $stage)
    {
        return $this->selectOne($this->getPlayersSelect($tournamentId, $stage));
    }

    public function countPlayers($tournamentId)
    {
        $subSelect = $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where($this->_db->quoteIdentifier('tournamentId') . ' = ?', $tournamentId)
            ->distinct('playerId');
        $select = $this->_db->select()
            ->from($subSelect, 'count(*)');

        return $this->selectOne($select);
    }
}

