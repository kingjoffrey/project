<?php

class Cli_Model_Game
{
    private $_id;

    private $_players = array();

    private $_turnNumber = 1;

    private $_ruins = array();

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_id = $gameId;

        $mGame = new Application_Model_Game($this->_id);
        $game = $mGame->getGame();

        $this->_mapId = $game['mapId'];

        $this->initPlayers($db);
        $this->initRuins($db);
    }

    private function initPlayers(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);
        foreach ($mPlayersInGame->getAllColors() as $playerId => $color) {
            $this->_players[$color] = new Cli_Model_Player($playerId, $this->_id, $db);
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

    private function initRuins(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mRuinsInGame = new Application_Model_RuinsInGame($this->_id, $db);
        $emptyRuins = $mRuinsInGame->getVisited();

        $mMapRuins = new Application_Model_MapRuins($this->_mapId, $db);
        foreach ($mMapRuins->getMapRuins() as $ruinId => $position) {
            if (isset($emptyRuins[$ruinId])) {
                $empty = true;
            } else {
                $empty = false;
            }
            $this->_ruins[$ruinId] = new Cli_Model_Ruin($position, $empty);
        }
    }

    public function updateRuins($ruinId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mRuinsInGame = new Application_Model_RuinsInGame($this->_id, $db);
        if ($mRuinsInGame->add($ruinId)) {

        }

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