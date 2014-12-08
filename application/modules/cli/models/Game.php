<?php

class Cli_Model_Game
{
    private $_id;

    private $_mapId;

    private $_turnNumber = 1;

    private $_capitals = array();
    private $_playersInGameColors;
    private $_online = array();

    private $_begin;
    private $_turnsLimit;
    private $_turnTimeLimit;
    private $_timeLimit;

    private $_turnHistory;
    private $_turnPlayerId;

    private $_me;

    private $_fields;
    private $_terrain;
    private $_units;
    private $_specialUnits = array();
    private $_firstUnitId;

    private $_players;
    private $_ruins;

    public function __construct($playerId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_l = new Coret_Model_Logger();

        $this->_players = new Cli_Model_Players();
        $this->_ruins = new Cli_Model_Ruins();
        $this->_id = $gameId;

        $mGame = new Application_Model_Game($this->_id, $db);
        $game = $mGame->getGame();

        $this->_mapId = $game['mapId'];
        $this->_begin = $game['begin'];
        $this->_turnsLimit = $game['turnsLimit'];
        $this->_turnTimeLimit = $game['turnTimeLimit'];
        $this->_timeLimit = $game['timeLimit'];

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
        $this->_fields = new Cli_Model_Fields($mMapFields->getMapFields());

        $mMapTerrain = new Application_Model_MapTerrain($this->_mapId, $db);
        $this->_terrain = $mMapTerrain->getTerrain();
        Zend_Registry::set('terrain', $this->_terrain);

        $mMapUnits = new Application_Model_MapUnits($this->_mapId, $db);
        $this->_units = $mMapUnits->getUnits();
        Zend_Registry::set('units', $this->_units);

        foreach ($this->_units as $unit) {
            if ($unit['special']) {
                $this->_specialUnits[] = $unit;
            }
        }

        reset($this->_units);
        $this->_firstUnitId = key($this->_units);

        $this->initPlayers($mMapPlayers, $db);
        $this->initRuins($db);

        $this->_me = new Cli_Model_Me(
            $this->getPlayerColor($playerId),
            $playerId
        );

        $this->_players->initFields($this->_fields);
    }

    private function initPlayers(Application_Model_MapPlayers $mMapPlayers, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);
        $players = $mPlayersInGame->getGamePlayers();
        $mMapCastles = new Application_Model_MapCastles($this->_mapId, $db);
        $mapCastles = $mMapCastles->getMapCastles();
        $mMapTowers = new Application_Model_MapTowers($this->_mapId, $db);
        $mapTowers = $mMapTowers->getMapTowers();

        foreach ($this->_playersInGameColors as $playerId => $color) {
            $this->_players->addPlayer($color, new Cli_Model_Player($players[$playerId], $this->_id, $mapCastles, $mapTowers, $mMapPlayers, $db));
        }

        $this->_players->addPlayer('neutral', new Cli_Model_NeutralPlayer($this->_mapId, $this->_id, $mapCastles, $db));
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
            $this->_ruins->add($ruinId, new Cli_Model_Ruin($position, $empty));
            $this->_fields->initRuin($position['x'], $position['y'], $ruinId, $empty);
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
            'units' => $this->_units,
            'firstUnitId' => $this->_firstUnitId,
            'specialUnits' => $this->_specialUnits,
            'fields' => $this->_fields->toArray(),
            'terrain' => $this->_terrain,
            'capitals' => $this->_capitals,
            'playersInGameColors' => $this->_playersInGameColors,
            'online' => $this->_online,
            'chatHistory' => $this->_chatHistory,
            'turnHistory' => $this->_turnHistory,
            'me' => $this->_me->toArray(),
            'players' => $this->_players->toArray(),
            'ruins' => $this->_ruins->toArray()
        );
    }

    public function getPlayerCapital($color)
    {
        return $this->_capitals[$color];
    }

    /**
     * @return Cli_Model_Fields
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * @return Cli_Model_Ruins
     */
    public function getRuins()
    {
        return $this->_ruins;
    }

    /**
     * @return Cli_Model_Players
     */
    public function getPlayers()
    {
        return $this->_players;
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

    public function getSpecialUnits()
    {
        return $this->_specialUnits;
    }

    public function getPlayersInGameColors()
    {
        return $this->_playersInGameColors;
    }

    public function getBegin()
    {
        return $this->_begin;
    }
}