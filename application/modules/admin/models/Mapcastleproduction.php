<?php

class Admin_Model_Mapcastleproduction extends Coret_Model_ParentDb
{
    protected $_name = 'mapcastleproduction';
    protected $_primary = 'mapCastleProductionId';
    protected $_sequence = 'mapcastleproduction_mapCastleProductionId_seq';

    protected $_columns = array(
        'mapCastleProductionId' => array('label' => 'Primary key', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'castleId' => array('label' => 'Castle ID', 'type' => 'select'),
        'unitId' => array('label' => 'Unit ID', 'type' => 'select'),
        'time' => array('label' => 'Czas', 'type' => 'number'),
    );


    static public function getCastleIdArray()
    {
        $m = new Admin_Model_Mapcastles();
        return $m->getList4FormSelect('name');
    }

    static public function getUnitIdArray()
    {
        $m = new Admin_Model_Unit();
        return $m->getList4FormSelect('unitId');
    }
}

