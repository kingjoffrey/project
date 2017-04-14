<?php

class Admin_Model_Tournament extends Coret_Model_ParentDb
{
    protected $_name = 'tournament';
    protected $_primary = 'tournamentId';
    protected $_sequence = 'tournament_tournamentId_seq';

    protected $_columns = array(
        'tournamentId' => array('label' => 'ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'start' => array('label' => 'Rozpoczęcie', 'type' => 'date'),
        'limit' => array('label' => 'Limit graczy', 'type' => 'number'),
    );
}
