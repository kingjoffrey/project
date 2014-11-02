<?php

class Cli_Model_Player
{
    private $_id;

    private $_armies = array();
    private $_castles = array();
    private $_towers = array();

    private $_isPlayerTurn = false;
    private $_isComputer = false;
    private $_lost = false;
    private $_miniMapColor;
    private $_backgroundColor;
    private $_textColor;
    private $_longName;
    private $_team;

    public function __construct($player, $gameId, Application_Model_MapPlayers $mMapPlayers, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_id = $player['playerId'];

        $this->init($gameId, $db);

        $this->_isPlayerTurn = $player['turnActive'];
        $this->_isComputer = $player['computer'];
        $this->_lost = $player['lost'];
        $this->_miniMapColor = $player['minimapColor'];
        $this->_backgroundColor = $player['backgroundColor'];
        $this->_textColor = $player['textColor'];
        $this->_longName = $player['longName'];

        $this->_team = $mMapPlayers->getColorByMapPlayerId($player['team']);
    }

    private function init($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        if ($this->_lost) {
            return;
        }
        $mArmy = new Application_Model_Army($gameId, $db);
        foreach ($mArmy->getPlayerArmies($this->_id) as $army) {
            $this->_armies[$army['armyId']] = new Cli_Model_Army($army);
        }
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        foreach ($mCastlesInGame->getPlayerCastles($this->_id) as $castle) {
            $this->_castles[$castle['castleId']] = $castle;
        }
        $mTowersInGame = new Application_Model_TowersInGame($gameId, $db);
        foreach ($mTowersInGame->getPlayerTowers($this->_id) as $tower) {
            $this->_towers[$tower['towerId']] = new Cli_Model_Tower($tower);
        }
    }

    public function updateArmy($army)
    {
        if (isset($this->_armies[$army->id])) {
            if ($army->destroyed) {
                unset($this->_armies[$army->id]);
            } else {
                $this->_armies[$army->id]->update($army);
            }
        } else {
            $this->_armies[$army->id] = new Cli_Model_Army($army);
        }
    }

    public function updateCastle($castle)
    {
        $this->_castles[$castle->id]->update($castle);
    }

    public function updateTower($tower)
    {
        $this->_towers[$tower->id]->update($tower);
    }

}