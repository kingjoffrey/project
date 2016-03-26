<?php

class Admin_Model_Help extends Coret_Model_ParentDb
{
    protected $_name = 'help';
    protected $_primary = 'helpId';
    protected $_sequence = 'help_helpId_seq';

    protected $_columns = array(
        'helpId' => array('label' => 'Side ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'menu' => array('label' => 'Akcja menu', 'type' => 'select'),
    );

    protected $_columns_lang = array(
        'title' => array('label' => 'Tytuł', 'type' => 'varchar'),
        'content' => array('label' => 'Treść', 'type' => 'text'),
    );

    static public function getMenuArray()
    {
        return array(
            'game' => 'Game',
            'castle' => 'Castle',
            'army' => 'Army',
            'units' => 'Units',
            'hero' => 'Hero',
            'ruin' => 'Ruin',
            'tower' => 'Tower',
            'terrain' => 'Terrain',
        );
    }
}
