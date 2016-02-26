<?php

class Admin_Model_Mapplayers extends Coret_Model_ParentDb
{
    protected $_name = 'mapplayers';
    protected $_primary = 'mapPlayerId';
    protected $_sequence = 'mapplayers_mapPlayerId_seq';

    protected $_columns = array(
        'mapId' => array('label' => 'Map ID', 'type' => 'number'),
        'startOrder' => array('label' => 'Kolejność', 'type' => 'number'),
        'sideId' => array('label' => 'Strona konfliktu', 'type' => 'select'),
    );

    static public function getSideIdArray()
    {
        $arr = array();
        $mSide = new Application_Model_Side();
        foreach ($mSide->getAll() as $row) {
            $arr[$row['sideId']] = $row['longName'];
        }
        return $arr;
    }

}

