<?php

class Cli_Model_Game
{
    private $_id;

    private $_players = array();

    private $_turnNumber = 1;

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_id = $gameId;

        $this->initPlayersArmies($db);
    }

    private function initPlayers(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);
        foreach ($mPlayersInGame->getAllColors() as $playerId => $color) {
            $this->_players[$color] = new Cli_Model_Player($playerId, $db);
        }
    }

    public function updatePlayerArmy($army, $color)
    {
        $this->_players[$color]->updateArmy($army);
    }

    public function updatePlayerTower($tower, $color)
    {
        $this->_players[$color]->updateTower($tower);
    }

    public function incrementTurnNumber()
    {
        $this->_turnNumber++;
    }

    public function getTurnNumber()
    {
        return $this->_turnNumber;
    }
}