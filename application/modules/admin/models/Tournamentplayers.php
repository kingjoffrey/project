<?php

class Admin_Model_Tournamentplayers extends Coret_Model_ParentDb
{
    protected $_name = 'tournamentplayers';

    protected $_columns = array(
        'tournamentId' => array('label' => 'Tournament ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'playerId' => array('label' => 'Player ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'stage' => array('label' => 'Etap', 'type' => 'number')
    );
}
