<?php

class Application_Model_BattleSequence extends Coret_Db_Table_Abstract
{
    protected $_name = 'battlesequence';
    protected $_primary = 'battleSequenceId';
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

    public function initiate($playerId, $units)
    {
        $sequence = 0;
        foreach ($units as $unitId => $unit) {
            if ($unit['canSwimm']) {
                continue;
            }
            $sequence++;
            $this->add($playerId, $unitId, $sequence);
        }
        $sequence++;
        // hero
        $this->add($playerId, 0, $sequence);
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

    public function edit($playerId, $unitId, $sequence)
    {
        $data = array(
            'unitId' => $unitId
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId),
            $this->_db->quoteInto('sequence = ?', $sequence)
        );

        $result = $this->update($data, $where);

        if ($result == 1) {
            return 1;
        }
    }

    public function get($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'unitId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId)
            ->order('sequence');

        $sequence = array();

        foreach ($this->selectAll($select) as $row) {
            $sequence[] = $row['unitId'];
        }

        return $sequence;
    }
}

