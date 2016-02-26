<?php

class Admin_Model_Mapcastles extends Coret_Model_ParentDb
{
    protected $_name = 'mapcastles';
    protected $_primary = 'mapCastleId';
    protected $_sequence = 'mapcastles_mapCastleId_seq';
    protected $_columns = array(
        'mapCastleId' => array('label' => 'Map castle ID', 'type' => 'number'),
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

//    public function castles($mapId)
//    {
//        $mCastle = new Admin_Model_Castle();
//        $castles = $mCastle->getCastles();
//
//        foreach ($castles as $castle) {
//            $data = array(
//                'x' => $castle['x'],
////                'y' => $castle['y'] - 79,
//                'y' => $castle['y'],
//            );
//
//            $where = array(
//                $this->_db->quoteInto('"mapId" = ?', $mapId),
//                $this->_db->quoteInto('"castleId" = ?', $castle['castleId'])
//            );
//
//            $this->update($data, $where);
//
//        }
//    }

}

