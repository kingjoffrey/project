<?php

class Application_Model_MapCastleProduction extends Coret_Db_Table_Abstract
{
    protected $_name = 'mapcastleproduction';
    protected $_primary = 'mapCastleProductionId';
    protected $_sequence = 'mapcastleproduction_mapCastleProductionId_seq';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getCastleProduction($castleId)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('time', 'unitId'))
            ->where($this->_db->quoteIdentifier('castleId') . ' = ?', $castleId);

        $production = array();

        foreach ($this->selectAll($select) as $val) {
            $production[$val['unitId']] = $val;
        }

        return $production;
    }

    public function addCastleProduction($castleId, $slot)
    {
        $data = array(
            'castleId' => $castleId,
            'unitId' => $slot['unitId'],
            'time' => $slot['time'],
        );

        return $this->insert($data);
    }

    public function editCastleProduction($castleId, $oldUnitId, $slot)
    {
        $data = array(
            'unitId' => $slot['unitId'],
            'time' => $slot['time'],
        );
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('castleId') . ' = ?', $castleId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('unitId') . ' = ?', $oldUnitId)
        );
        return $this->update($data, $where);
    }

    public function removeCastleProduction($castleId, $unitId)
    {
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('castleId') . ' = ?', $castleId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('unitId') . ' = ?', $unitId)
        );
        return $this->delete($where);
    }
}

