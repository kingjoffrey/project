<?php

class Application_Model_MapCastlesProduction extends Game_Db_Table_Abstract
{
    protected $_name = 'mapcastlesproduction';
    protected $_primary = 'mapCastleProductionId';
    protected $_sequence = '';
    protected $mapCastleId;

    public function __construct($db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getCastleProduction($mapCastleId)
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier('mapCastleId') . ' = ?', $mapCastleId);

        try {
            $all = $this->selectAll($select);
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }

        $production = array();
        foreach ($all as $val) {
            $production[$val['unitId']] = $val;
        }

        return $production;
    }
}
