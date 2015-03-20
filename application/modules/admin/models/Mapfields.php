<?php

class Admin_Model_Mapcastles extends Coret_Model_ParentDb
{
    protected $_name = 'mapfields';
    protected $_primary = 'mapFieldId';
    protected $_sequence = 'mapcastles_mapCastleId_seq';
    protected $_columns = array(
        'mapCastleId' => array('label' => 'Map castle ID', 'type' => 'number'),
        'castleId' => array('label' => 'Castle ID', 'type' => 'select'),
        'mapId' => array('label' => 'Map ID', 'type' => 'select'),
        'x' => array('label' => 'X', 'type' => 'number'),
        'y' => array('label' => 'Y', 'type' => 'number'),
        'enclaveNumber' => array('label' => 'Enclave number', 'type' => 'number')
    );

    static public function getMapIdArray()
    {
        $mMap = new Application_Model_Map();
        return $mMap->getAllMapsList();
    }

    static public function getCastleIdArray()
    {
        $mCastle = new Application_Model_Castle();
        return $mCastle->getAll();
    }
}

