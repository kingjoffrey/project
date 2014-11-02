<?php

class Cli_Model_Player
{
    private $_id;

    private $_armies = array();
    private $_towers = array();
    private $_castles = array();
    private $_ruins = array();

    public function __construct($playerId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_id = $playerId;

        $this->initArmies($gameId, $db);
    }

    private function initArmies($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        foreach ($mArmy->getPlayerArmies($this->_id) as $army) {
            $this->_armies[$army['armyId']] = new Cli_Model_Army($army);
        }
    }

    public function updateArmy($army)
    {
        $this->_armies[$army->id]->update($army);
    }
}