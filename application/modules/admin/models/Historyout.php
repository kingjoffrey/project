<?php

class Admin_Model_Historyout extends Coret_Model_ParentDb
{
    protected $_name = 'tokensout';
    protected $_primary = 'tokensoutId';
    protected $_columns = array(
        'gameId' => array('label' => 'Game Id', 'type' => 'number'),
        'data' => array('label' => 'Data', 'type' => 'text'),
        'type' => array('label' => 'Type', 'type' => 'varchar'),
        'date' => array('label' => 'Date', 'type' => 'date'),
        'attackerColor' => array('label' => 'A color', 'type' => 'varchar'),
        'attackerArmy' => array('label' => 'A army', 'type' => 'text'),
        'defenderColor' => array('label' => 'D color', 'type' => 'varchar'),
        'defenderArmy' => array('label' => 'D army', 'type' => 'text'),
        'path' => array('label' => 'Path', 'type' => 'text'),
        'battle' => array('label' => 'Battle', 'type' => 'text'),
        'oldArmyId' => array('label' => 'oldArmyId', 'type' => 'number'),
        'deletedIds' => array('label' => 'deletedIds', 'type' => 'varchar'),
        'victory' => array('label' => 'victory', 'type' => 'checkbox'),
        'castleId' => array('label' => 'castleId', 'type' => 'number'),
        'ruinId' => array('label' => 'ruinId', 'type' => 'number'),
        'lost' => array('label' => 'lost', 'type' => 'checkbox'),
        'win' => array('label' => 'win', 'type' => 'checkbox'),
        'gold' => array('label' => 'gold', 'type' => 'varchar'),
        'costs' => array('label' => 'costs', 'type' => 'number'),
        'income' => array('label' => 'income', 'type' => 'number'),
        'armies' => array('label' => 'armies', 'type' => 'varchar'),
        'nr' => array('label' => 'nr', 'type' => 'number'),
        'action' => array('label' => 'action', 'type' => 'varchar'),
        'color' => array('label' => 'color', 'type' => 'varchar'),
        'x' => array('label' => 'x', 'type' => 'number'),
        'y' => array('label' => 'y', 'type' => 'number'),
    );
    protected $_columns_lang = array();
    protected $_order = 'data';
}