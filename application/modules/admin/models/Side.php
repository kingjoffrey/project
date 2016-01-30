<?php

class Admin_Model_Side extends Coret_Model_ParentDb
{
    protected $_name = 'side';
    protected $_primary = 'sideId';
    protected $_sequence = 'side_sideId_seq';

    protected $_columns = array(
        'sideId' => array('label' => 'Side ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'longName' => array('label' => 'Długa nazwa', 'type' => 'varchar'),
        'shortName' => array('label' => 'Krótka nazwa', 'type' => 'varchar'),
        'minimapColor' => array('label' => 'Kolor minimapy', 'type' => 'varchar'),
        'backgroundColor' => array('label' => 'Kolor tła', 'type' => 'varchar'),
        'textColor' => array('label' => 'Kolor tekstu', 'type' => 'varchar')
    );
}

