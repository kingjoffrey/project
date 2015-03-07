<?php

class Application_Model_UnitsInGame extends Coret_Db_Table_Abstract
{
    protected $_name = 'unitsingame';
    protected $_primary = 'soldierId';
    protected $gameId;
    protected $_unit = 'unit';
    protected $_sequence = 'soldier_soldierId_seq';

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function add($armyId, $unitId)
    {
        $units = Zend_Registry::get('units');

        $data = array(
            'armyId' => $armyId,
            'gameId' => $this->_gameId,
            'unitId' => $unitId,
            'movesLeft' => $units[$unitId]['numberOfMoves']
        );

        $this->insert($data);
        return $this->_db->lastSequenceId($this->_sequence);
    }

    public function updateMovesLeft($movesLeft, $soldierId)
    {
        $data = array(
            'movesLeft' => $movesLeft
        );

        $where = $this->_db->quoteInto('"soldierId" = ?', $soldierId);

        $this->update($data, $where);
    }

    public function getForMove($armyId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('movesLeft', 'soldierId', 'unitId'))
            ->join(array('b' => $this->_unit), 'a."unitId" = b."unitId"', null)
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"armyId" = ?', $armyId)
            ->order(array('canFly DESC', 'attackPoints DESC', 'defensePoints DESC', 'movesLeft DESC', 'numberOfMoves DESC', 'unitId DESC'));

        return $this->selectAll($select);
    }

    public function destroy($soldierId)
    {
        $where = array(
            $this->_db->quoteInto('"soldierId" = ?', $soldierId),
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId)
        );

        $this->delete($where);
    }

    public function soldiersUpdateArmyId($oldArmyId, $newArmyId)
    {
        $data = array(
            'armyId' => $newArmyId
        );

        $where = array(
            $this->_db->quoteInto('"armyId" = ?', $oldArmyId),
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId)
        );

        return $this->update($data, $where);
    }

    public function soldierUpdateArmyId($soldierId, $newArmyId)
    {
        $data = array(
            'armyId' => $newArmyId
        );

        $where = array(
            $this->_db->quoteInto('"soldierId" = ?', $soldierId),
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId)
        );

        return $this->update($data, $where);
    }


}

