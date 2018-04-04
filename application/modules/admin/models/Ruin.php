<?php

class Admin_Model_Ruin extends Coret_Model_ParentDb
{
    protected $_name = 'ruin';
    protected $_primary = 'ruinId';
    protected $_sequence = 'ruin_ruinId_seq';

    protected $_columns = array(
        'ruinId' => array('label' => 'Ruin ID', 'type' => 'number'),
        'type' => array('label' => 'Typ', 'type' => 'varchar'),
    );

}

