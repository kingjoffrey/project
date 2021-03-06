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

    public function updateStage($tournamentId, $playerId, $stage = null)
    {
        if ($stage === null) {
            $data = array(
                'stage' => new Zend_Db_Expr('stage + 1')
            );
        } else {
            $data = array(
                'stage' => $stage
            );
        }

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

    public function playerPayment($tournamentId, $playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where($this->_db->quoteIdentifier('tournamentId') . ' = ?', $tournamentId)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
            ->where('stage > 0');

        return $this->selectOne($select);
    }

    public function getPlayersNames($tournamentId)
    {
        $playerIden = $this->_db->quoteIdentifier('playerId');

        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('stage', 'playerId'))
            ->join(array('b' => 'player'), 'a.' . $playerIden . ' = b.' . $playerIden, array('firstName', 'lastName'))
            ->where($this->_db->quoteIdentifier('tournamentId') . ' = ?', $tournamentId)
            ->distinct('a.' . $playerIden)
            ->order('stage DESC');

        $array = array();

        foreach ($this->selectAll($select) as $row) {
            $array[] = array(
                'name' => $row['firstName'] . ' ' . $row['lastName'],
                'id' => $row['playerId'],
                'stage' => $row['stage']
            );
        }

        return $array;
    }

    public function getPlayersId($tournamentId)
    {
        $subSelect = $this->_db->select()
            ->from($this->_name, 'max(stage)')
            ->where($this->_db->quoteIdentifier('tournamentId') . ' = ?', $tournamentId);

        $select = $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where($this->_db->quoteIdentifier('tournamentId') . ' = ?', $tournamentId)
            ->where('stage = (?)', new Zend_Db_Expr($subSelect))
            ->distinct('playerId');

        return $this->selectAll($select);
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

