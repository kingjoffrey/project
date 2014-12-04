<?php

class Admin_Model_Army extends Coret_Model_ParentDb
{
    protected $_name = 'army';
    protected $_primary = 'armyId';
    protected $_sequence = 'army_armyId_seq';

    protected $_columns = array(
        'armyId' => array('label' => 'Army ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'gameId' => array('label' => 'Game ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'playerId' => array('label' => 'Player ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'x' => array('label' => 'X', 'type' => 'number'),
        'y' => array('label' => 'Y', 'type' => 'number'),
        'fortified' => array('label' => 'Fortyfikacja', 'type' => 'checkbox'),
        'destroyed' => array('label' => 'Zniszczona', 'type' => 'checkbox'),
    );
}

