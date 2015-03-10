<?php

class Cli_Model_Game
{
    private $_id;
    private $_mapId;

    private $_capitals = array();
    private $_playersInGameColors;
    private $_online = array();
    private $_numberOfGarrisonUnits;

    private $_begin;
    private $_turnsLimit;
    private $_turnTimeLimit;
    private $_timeLimit;

    private $_turnHistory;
    private $_turnNumber;
    private $_turnPlayerId;

    private $_Fields;
    private $_terrain;
    private $_Units;
    private $_firstUnitId;
    private $_Players;
    private $_Ruins;

    private $_loaded = false;

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_l = new Coret_Model_Logger();

        $this->_Players = new Cli_Model_Players();
        $this->_Ruins = new Cli_Model_Ruins();
        $this->_id = $gameId;

        $mGame = new Application_Model_Game($this->_id, $db);
        $game = $mGame->getGame();

        $this->_mapId = $game['mapId'];
        $this->_begin = $game['begin'];
        $this->_turnsLimit = $game['turnsLimit'];
        $this->_turnTimeLimit = $game['turnTimeLimit'];
        $this->_timeLimit = $game['timeLimit'];

        $this->_turnNumber = $game['turnNumber'];
        $this->_turnPlayerId = $game['turnPlayerId'];

        $mTurnHistory = new Application_Model_TurnHistory($this->_id, $db);
        $this->_turnHistory = $mTurnHistory->getTurnHistory();

        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);
        $this->_playersInGameColors = Zend_Registry::get('playersInGameColors');

        foreach ($mPlayersInGame->getInGamePlayerIds() as $row) {
            $this->_online[$this->_playersInGameColors[$row['playerId']]] = 1;
        }

        $mChat = new Application_Model_Chat($this->_id, $db);
        $this->_chatHistory = $mChat->getChatHistory();
        foreach ($this->_chatHistory as $k => $v) {
            $this->_chatHistory[$k]['color'] = $this->_playersInGameColors[$v['playerId']];
        }

        $mMapPlayers = new Application_Model_MapPlayers($this->_mapId, $db);
        $this->_capitals = $mMapPlayers->getCapitals();

        $mMapFields = new Application_Model_MapFields($this->_mapId, $db);
        $this->_Fields = new Cli_Model_Fields($mMapFields->getMapFields());

        $mMapTerrain = new Application_Model_MapTerrain($this->_mapId, $db);
        $this->_terrain = $mMapTerrain->getTerrain();
        Zend_Registry::set('terrain', $this->_terrain);

        $mMapUnits = new Application_Model_MapUnits($this->_mapId, $db);
        $this->_Units = new Cli_Model_Units();
        foreach ($mMapUnits->getUnits() as $unitId => $unit) {
            $this->_Units->add($unitId, new Cli_Model_Unit($unit));
        }
        Zend_Registry::set('units', $this->_Units);

        $this->_firstUnitId = $this->_Units->getFirstUnitId();

        $this->initPlayers($mMapPlayers, $db);
        $this->initRuins($db);

        $this->_Players->initFields($this->_Fields);
        $this->updateNumberOfGarrisonUnits();

        $this->_loaded = true;
    }

    private function initPlayers(Application_Model_MapPlayers $mMapPlayers, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);
        $players = $mPlayersInGame->getGamePlayers();
        $mMapCastles = new Application_Model_MapCastles($this->_mapId, $db);
        $mapCastles = $mMapCastles->getMapCastles();
        $mMapTowers = new Application_Model_MapTowers($this->_mapId, $db);
        $mapTowers = $mMapTowers->getMapTowers();
        $mTowersInGame = new Application_Model_TowersInGame($this->_id, $db);
        $playersTowers = $mTowersInGame->getTowers();

        foreach ($this->_playersInGameColors as $playerId => $color) {
            $this->_Players->addPlayer($color, new Cli_Model_Player($players[$playerId], $this->_id, $mapCastles, $mapTowers, $playersTowers, $mMapPlayers, $db));
        }
        $this->_Players->addPlayer('neutral', new Cli_Model_NeutralPlayer($this->_id, $mapCastles, $mapTowers, $playersTowers, $db));
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
            $position['ruinId'] = $ruinId;
            $this->_Ruins->add($ruinId, new Cli_Model_Ruin($position, $empty));
            $this->_Fields->getField($position['x'], $position['y'])->setRuin($ruinId, $empty);
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

    public function getTimeLimit()
    {
        return $this->_timeLimit;
    }

    public function getTurnTimeLimit()
    {
        return $this->_turnTimeLimit;
    }

    public function toArray()
    {
        return array(
            'begin' => $this->_begin,
            'timeLimit' => $this->_timeLimit,
            'turnsLimit' => $this->_turnsLimit,
            'turnTimeLimit' => $this->_turnTimeLimit,
            'turnNumber' => $this->_turnNumber,
            'units' => $this->_Units->toArray(),
            'firstUnitId' => $this->_firstUnitId,
//            'specialUnits' => $this->_specialUnits,
            'fields' => $this->_Fields->toArray(),
            'terrain' => $this->_terrain,
            'capitals' => $this->_capitals,
//            'playersInGameColors' => $this->_playersInGameColors,
            'online' => $this->_online,
            'chatHistory' => $this->_chatHistory,
            'turnHistory' => $this->_turnHistory,
            'players' => $this->_Players->toArray(),
            'ruins' => $this->_Ruins->toArray()
        );
    }

    public function getPlayerCapitalId($color)
    {
        return $this->_capitals[$color];
    }

    /**
     * @return Cli_Model_Fields
     */
    public function getFields()
    {
        return $this->_Fields;
    }

    /**
     * @return Cli_Model_Ruins
     */
    public function getRuins()
    {
        return $this->_Ruins;
    }

    /**
     * @return Cli_Model_Players
     */
    public function getPlayers()
    {
        return $this->_Players;
    }

    /**
     * @return Cli_Model_Units
     */
    public function getUnits()
    {
        return $this->_Units;
    }

    public function getPlayerColor($playerId)
    {
        if ($playerId) {
            return $this->_playersInGameColors[$playerId];
        } else {
            return 'neutral';
        }
    }

    public function turnNumberIncrement()
    {
        $this->_turnNumber++;
        $this->updateNumberOfGarrisonUnits();
    }

    public function setTurnPlayerId($playerId)
    {
        $this->_turnPlayerId = $playerId;
    }

    public function getTurnPlayerId()
    {
        return $this->_turnPlayerId;
    }

    public function isPlayerTurn($playerId)
    {
        return $this->_turnPlayerId == $playerId;
    }

    public function getTurnsLimit()
    {
        return $this->_turnsLimit;
    }

    public function getMe()
    {
        return $this->_me;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getFirstUnitId()
    {
        return $this->_firstUnitId;
    }

   public function getPlayersInGameColors()
    {
        return $this->_playersInGameColors;
    }

    public function getBegin()
    {
        return $this->_begin;
    }

    private function updateNumberOfGarrisonUnits()
    {
        $this->_numberOfGarrisonUnits = floor($this->_turnNumber / 7 + 0.5);
        if ($this->_numberOfGarrisonUnits > 4) {
            $this->_numberOfGarrisonUnits = 4;
        }
    }

    public function getNumberOfGarrisonUnits()
    {
        return $this->_numberOfGarrisonUnits;
    }

    public function isLoaded()
    {
        return $this->_loaded;
    }
}