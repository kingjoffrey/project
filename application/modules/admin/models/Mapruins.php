<?php

class Admin_Model_Mapruins extends Coret_Model_ParentDb
{
    protected $_name = 'mapruins';
    protected $_primary = 'mapRuinId';
    protected $_sequence = 'mapruins_mapRuinId_seq';

    protected $_columns = array(
        'mapRuinId' => array('label' => 'Ruin ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'mapId' => array('label' => 'Map ID', 'type' => 'select'),
        'x' => array('label' => 'X', 'type' => 'number'),
        'y' => array('label' => 'Y', 'type' => 'number'),
        'ruinId' => array('label' => 'Typ', 'type' => 'select'),
    );


    static public function getMapIdArray()
    {
        $mRuin = new Admin_Model_Map();
        return $mRuin->getList4FormSelect('name');

    }

    static public function getRuinIdArray()
    {
        $mRuin = new Admin_Model_Ruin();
        return $mRuin->getList4FormSelect('type');

    }
}

