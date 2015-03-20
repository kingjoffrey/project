<?php

class Admin_Model_Mapfields extends Coret_Model_ParentDb
{
    protected $_name = 'mapfields';
    protected $_primary = 'mapFieldId';
    protected $_sequence = 'mapfields_mapFieldId_seq';
    protected $_columns = array(
        'mapId' => array('label' => 'Map ID', 'type' => 'select'),
        'x' => array('label' => 'X', 'type' => 'number'),
        'y' => array('label' => 'Y', 'type' => 'number'),
        'type' => array('label' => 'Type', 'type' => 'varchar')
    );

    static public function getMapIdArray()
    {
        $mMap = new Application_Model_Map();
        return $mMap->getAllMapsList();
    }
}

