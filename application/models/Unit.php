<?php

class Application_Model_Unit extends Coret_Db_Table_Abstract
{

    protected $_name = 'unit';
    protected $_primary = 'unitId';

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getUnits()
    {
        $units = array();
//        $units = array(null);

        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('attackPoints', 'defensePoints', 'canFly', 'canSwim', 'cost', 'modMovesForest', 'modMovesHills', 'modMovesSwamp', 'numberOfMoves', 'unitId', 'special'))
            ->join(array('b' => 'unit_Lang'), 'b.' . $this->_db->quoteIdentifier('unitId') . ' = a.' . $this->_db->quoteIdentifier('unitId'), 'name')
            ->where('id_lang = ?', Zend_Registry::get('config')->id_lang)
            ->order(array('attackPoints', 'defensePoints', 'numberOfMoves'));

        foreach ($this->selectAll($select) as $key => $unit) {
            $select = $this->_db->select()
                ->from('unit_Lang', 'name')
                ->where('id_lang = ?', Zend_Registry::get('id_lang'))
                ->where($this->_db->quoteIdentifier('unitId') . ' = ?', $unit['unitId']);

            $unit['name_lang'] = $this->selectOne($select);

            $units[$key] = $unit;
        }

        return $units;
    }

    public function getUnitsNames()
    {
        $unitId = $this->_db->quoteIdentifier('unitId');

        $select = $this->_db->select()
            ->from(array('a' => $this->_name), null)
            ->join(array('b' => 'unit_Lang'), 'b.' . $unitId . ' = a.' . $unitId, 'name')
            ->where('id_lang = ?', Zend_Registry::get('config')->id_lang);

        return $this->selectAll($select);
    }

    public function getUnitIdByName($name)
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where('name = ?', $name);

        return $this->selectOne($select);
    }
}

