<?php

class Application_Model_Army extends Coret_Db_Table_Abstract
{

    protected $_name = 'army';
    protected $_primary = 'armyId';
    protected $_gameId;

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function createArmy($position, $playerId, $sleep = 0)
    {
        $armyId = $this->getNewArmyId();
        $data = array(
            'armyId' => $armyId,
            'playerId' => $playerId,
            'gameId' => $this->_gameId,
            'x' => $position['x'],
            'y' => $position['y']
        );
        try {
            $this->insert($data);
            return $armyId;
        } catch (Exception $e) {
            if ($sleep > 10) {
                throw new Exception($e->getMessage());
            }
            sleep(rand(0, $sleep));
            $armyId = $this->createArmy($position, $playerId, $sleep + 1);
        }
        return $armyId;
    }

    private function getNewArmyId()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'max("armyId")')
            ->where('"gameId" = ?', $this->_gameId);
        return $this->selectOne($select) + 1;
    }

    public function getPlayerArmies($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('armyId', 'fortified', 'x', 'y'))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false');

        return $this->selectAll($select);
    }

    public function destroyArmy($armyId)
    {
        $data = array(
            'destroyed' => 'true'
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier($this->_primary) . ' = ?', $armyId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
        );

        return $this->update($data, $where);
    }

    public function fortify($armyId, $fortify, $playerId = null)
    {
        if ($fortify) {
            $data = array(
                'fortified' => 't'
            );
        } else {
            $data = array(
                'fortified' => 'f'
            );
        }

        $where = array(
            $this->_db->quoteInto('"armyId" = ?', $armyId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
        );

        if ($playerId) {
            $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        }

        return $this->update($data, $where);
    }

    public function updateArmyPosition($end, $armyId)
    {
        $data = array(
            'x' => $end['x'],
            'y' => $end['y'],
            'fortified' => 'false'
        );
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('armyId') . ' = ?', $armyId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
        );

        return $this->update($data, $where);
    }
}
