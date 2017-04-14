<?php

class Admin_Model_Tutorial extends Coret_Model_ParentDb
{
    protected $_name = 'tutorial';
    protected $_primary = 'tutorialId';
    protected $_sequence = 'tutorial_tutorialId_seq';

    protected $_columns = array(
        'tutorialId' => array('label' => 'ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'number' => array('label' => 'Numer', 'type' => 'number'),
        'step' => array('label' => 'Krok', 'type' => 'number'),
    );

    protected $_columns_lang = array(
        'goal' => array('label' => 'Cel', 'type' => 'varchar'),
        'description' => array('label' => 'Opis', 'type' => 'text'),
    );
}
