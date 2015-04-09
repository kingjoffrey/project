<?php

class Cli_Model_Game
{
    private $_id;
    private $_mapId;

    private $_capitals = array();
    private $_playersInGameColors;

    private $_users = array();
    private $_online = array();

    private $_numberOfGarrisonUnits;
    private $_numberOfAllCastles = 0;

    private $_isActive;
    private $_begin;
    private $_turnsLimit;
    private $_turnTimeLimit;
    private $_timeLimit;

    private $_turnHistory;
    private $_turnNumber;
    private $_turnPlayerId;

    private $_Fields;
    private $_Terrain;
    private $_Units;
    private $_firstUnitId;
    private $_Players;
    private $_Ruins;

    public function __construct($gameId, $playersInGameColors, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_l = new Coret_Model_Logger();

        $this->_id = $gameId;
        $this->_playersInGameColors = $playersInGameColors;

        $mGame = new Application_Model_Game($this->_id, $db);
        $game = $mGame->getGame();

        $this->_isActive = $game['isActive'];
        if (!$this->_isActive) {
            return;
        }

        $this->_Players = new Cli_Model_Players();
        $this->_Ruins = new Cli_Model_Ruins();

        $this->_mapId = $game['mapId'];
        $this->_begin = $game['begin'];
        $this->_turnsLimit = $game['turnsLimit'];
        $this->_turnTimeLimit = $game['turnTimeLimit'];
        $this->_timeLimit = $game['timeLimit'];

        $this->_turnNumber = $game['turnNumber'];
        $this->_turnPlayerId = $game['turnPlayerId'];

        $mTurnHistory = new Application_Model_TurnHistory($this->_id, $db);
        $this->_turnHistory = $mTurnHistory->getTurnHistory();

        $mChat = new Application_Model_Chat($this->_id, $db);
        $this->_chatHistory = $mChat->getChatHistory();
        foreach ($this->_chatHistory as $k => $v) {
            $this->_chatHistory[$k]['color'] = $this->_playersInGameColors[$v['playerId']];
            unset($this->_chatHistory[$k]['playerId']);
        }

        $mMapPlayers = new Application_Model_MapPlayers($this->_mapId, $db);
        $this->_capitals = $mMapPlayers->getCapitals();

        $mMapFields = new Application_Model_MapFields($this->_mapId, $db);
        $this->_Fields = new Cli_Model_Fields($mMapFields->getMapFields());

        $mMapTerrain = new Application_Model_MapTerrain($this->_mapId, $db);
        $this->_Terrain = new Cli_Model_TerrainTypes($mMapTerrain->getTerrain());

        $mMapUnits = new Application_Model_MapUnits($this->_mapId, $db);
        $this->_Units = new Cli_Model_Units();
        foreach ($mMapUnits->getUnits() as $unitId => $unit) {
            $this->_Units->add($unitId, new Cli_Model_Unit($unit));
        }
        Zend_Registry::set('units', $this->_Units);

        $this->_firstUnitId = $this->_Units->getFirstUnitId();

        $this->updateNumberOfGarrisonUnits();
        $this->initPlayers($mMapPlayers, $db);
        $this->initRuins($db);
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
            $player = new Cli_Model_Player($players[$playerId], $this->_id, $mapCastles, $mapTowers, $playersTowers, $mMapPlayers, $db);
            $this->_Players->addPlayer($color, $player);
            if (!$player->getComputer()) {
                $this->updateOnline($color, 0);
            }
            $this->_numberOfAllCastles += $player->getCastles()->count();
        }
        $this->_Players->addPlayer('neutral', new Cli_Model_NeutralPlayer($this, $mapCastles, $mapTowers, $playersTowers, $db));
        $this->_Players->initFields($this->_Fields);


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
            $this->_Fields->getField($position['x'], $position['y'])->setRuin($ruinId);
        }
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
            'units' => $this->_Units->toArray(),
            'firstUnitId' => $this->_firstUnitId,
            'fields' => $this->_Fields->toArray(),
            'terrain' => $this->_Terrain->toArray(),
            'capitals' => $this->_capitals,
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
        $numberOfGarrisonUnits = $this->_numberOfGarrisonUnits;
        $this->_turnNumber++;
        $this->updateNumberOfGarrisonUnits();
        if ($numberOfGarrisonUnits < $this->_numberOfGarrisonUnits) {
            $this->_Players->getPlayer('neutral')->increaseCastlesGarrison($this->_numberOfGarrisonUnits, $this->_firstUnitId, $this->_Units);
        }
    }

    public function setTurnPlayerId($playerId)
    {
        $this->_turnPlayerId = $playerId;
        $this->_turnHistory[] = array(
            'date' => date('Y-m-d H:i:s'),
            'number' => $this->_turnNumber,
            'shortName' => $this->getPlayerColor($playerId)
        );
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

    public function updateChatHistory($message, $color)
    {
        $this->_chatHistory[] = array('date' => date('Y-m-d H:i:s', mktime()), 'message' => $message, 'color' => $color);
    }

    public function addUser($playerId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Application_Model_PlayersInGame $mPlayersInGame)
    {
        $mPlayersInGame->updateWSSUId($playerId, $user->getId());
        $this->_users[$playerId] = $user;
        $this->updateOnline($this->getPlayerColor($playerId), 1);
    }

    public function removeUser($playerId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);
        $mPlayersInGame->updateWSSUId($playerId, null);
        unset($this->_users[$playerId]);
        $this->updateOnline($this->getPlayerColor($playerId), 0);
    }

    public function getUsers()
    {
        return $this->_users;
    }

    private function updateOnline($color, $online)
    {
        $this->_online[$color] = $online;
    }

    public function playerHasMoreThanFiftyPercentOfCastles($color)
    {
        if ($this->_Players->getPlayer($color)->getCastles()->count() > $this->_numberOfAllCastles / 2) {
            return true;
        }
    }

    public function isActive()
    {
        return $this->_isActive;
    }

    public function getTerrain()
    {
        return $this->_Terrain;
    }

    /**
     * @param Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @return Cli_Model_Game
     */
    static public function getGame(Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        return $user->parameters['game'];
    }
}