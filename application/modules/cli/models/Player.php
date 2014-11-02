<?php

class Cli_Model_Player
{
    private $_id;

    private $_armies = array();
    private $_towers;
    private $_castles;
    private $_ruins;

    public function __construct($playerId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_id = $playerId;
    }

    public function updateArmy($army)
    {
        $this->_armies[$army->id]->update($army);
    }
}