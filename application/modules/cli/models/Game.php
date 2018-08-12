<?php

class Cli_Model_Game
{
    private $_id;

    private $_mapId;

    private $_playersColors;

    private $_users = array();
    private $_online = array();

    private $_numberOfGarrisonUnits;
    private $_numberOfNeutralGarrisonUnits = 1;
    private $_numberOfComputerArmyUnits;
    private $_numberOfAllCastles = 0;

    private $_isActive;
    private $_begin;

    private $_type;

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

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_Model_TerrainTypes $Terrain)
    {
        $this->_id = $gameId;

        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);
        $players = $mPlayersInGame->getGamePlayers();
        foreach ($players as $playerId => $player) {
            $this->_playersColors[$playerId] = $player['color'];
        }

        $mGame = new Application_Model_Game($this->_id, $db);
        $game = $mGame->getGame();

        $this->_type = $game['type'];
        $this->_isActive = $game['isActive'];
        if (!$this->_isActive) {
            return;
        }

        $this->_Players = new Cli_Model_Players();
        $this->_Ruins = new Cli_Model_GameRuins();

        $this->_begin = $game['begin'];
        $this->_turnsLimit = $game['turnsLimit'];
        $this->_turnTimeLimit = $game['turnTimeLimit'];
        $this->_timeLimit = $game['timeLimit'];

        $this->_turnNumber = $game['turnNumber'];
        $this->_turnPlayerId = $game['turnPlayerId'];

        $this->_mapId = $game['mapId'];

        $mMapFields = new Application_Model_MapFields($this->_mapId, $db);
        $this->_Fields = new Cli_Model_Fields($mMapFields->getMapFields());

        $this->_Terrain = $Terrain;

        $mUnit = new Application_Model_Unit($db);
        $this->_Units = new Cli_Model_Units();
        foreach ($mUnit->getUnits() as $unit) {
            $this->_Units->add($unit['unitId'], new Cli_Model_Unit($unit));
        }
        Zend_Registry::set('units', $this->_Units);

        $this->_firstUnitId = $this->_Units->getFirstUnitId();

        $this->updateNumberOfGarrisonUnits();
        $this->updateNumberOfNeutralGarrisonUnits();
        $this->updateNumberOfComputerArmyUnits();

        $this->initPlayers($players, $db);
        $this->initRuins($db);
    }

    private function initPlayers($players, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mMapCastles = new Application_Model_MapCastles($this->_mapId, $db);
        $mapCastles = $mMapCastles->getMapCastles();
        $mMapTowers = new Application_Model_MapTowers($this->_mapId, $db);
        $mapTowers = $mMapTowers->getMapTowers();
        $mTowersInGame = new Application_Model_TowersInGame($this->_id, $db);
        $playersTowers = $mTowersInGame->getTowers();

        foreach ($this->_playersColors as $playerId => $color) {
            $player = new Cli_Model_Player($players[$playerId], $this->_id, $mapCastles, $mapTowers, $playersTowers, $db);
            $this->_Players->addPlayer($color, $player);
            if (!$player->getComputer()) {
                $this->updateOnline($color, 0);
            }
            $this->_numberOfAllCastles += $player->getCastles()->count();
        }
        $player = new Cli_Model_NeutralPlayer($this, $mapCastles, $mapTowers, $playersTowers, $db);
        $this->_Players->addPlayer('neutral', $player);
        $this->_numberOfAllCastles += $player->getCastles()->count();

        $this->_Players->initFields($this->_Fields);
    }

    private function initRuins(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mRuinsInGame = new Application_Model_RuinsInGame($this->_id, $db);
        $emptyRuins = $mRuinsInGame->getVisited();

        $mMapRuins = new Application_Model_MapRuins($this->_mapId, $db);
        foreach ($mMapRuins->getMapRuins() as $mapRuinId => $mapRuin) {
            if (isset($emptyRuins[$mapRuinId])) {
                $empty = true;
            } else {
                $empty = false;
            }
            $mapRuin['mapRuinId'] = $mapRuinId;
            $this->_Ruins->add($mapRuinId, new Cli_Model_GameRuin($mapRuin, $empty));
            $this->_Fields->getField($mapRuin['x'], $mapRuin['y'])->setRuin($mapRuinId);
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
            'version' => Zend_Registry::get('config')->version,
            'begin' => $this->_begin,
            'timeLimit' => $this->_timeLimit,
            'turnsLimit' => $this->_turnsLimit,
            'turnTimeLimit' => $this->_turnTimeLimit,
            'units' => $this->_Units->toArray(),
            'firstUnitId' => $this->_firstUnitId,
            'fields' => $this->_Fields->toArray(),
            'turnColor' => $this->getPlayerColor($this->_turnPlayerId),
            'turnNumber' => $this->_turnNumber,
            'players' => $this->_Players->toArray(),
            'ruins' => $this->_Ruins->toArray()
        );
    }

    /**
     * @return Cli_Model_Fields
     */
    public function getFields()
    {
        return $this->_Fields;
    }

    /**
     * @return Cli_Model_GameRuins
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
            return $this->_playersColors[$playerId];
        } else {
            return 'neutral';
        }
    }

    public function turnNumberIncrement()
    {
        $numberOfNeutralGarrisonUnits = $this->_numberOfNeutralGarrisonUnits;
        $this->_turnNumber++;
        $this->updateNumberOfGarrisonUnits();
        $this->updateNumberOfNeutralGarrisonUnits();
        if ($numberOfNeutralGarrisonUnits < $this->_numberOfNeutralGarrisonUnits) {
            $this->_Players->getPlayer('neutral')->increaseCastlesGarrison($this->_numberOfNeutralGarrisonUnits, $this->_firstUnitId, $this->_Units);
        }
        $this->updateNumberOfComputerArmyUnits();
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

    public function getPlayersColors()
    {
        return $this->_playersColors;
    }

    public function getBegin()
    {
        return $this->_begin;
    }

    private function updateNumberOfGarrisonUnits()
    {
        $this->_numberOfGarrisonUnits = floor($this->_turnNumber / 4);
        if ($this->_numberOfGarrisonUnits > 4) {
            $this->_numberOfGarrisonUnits = 4;
        }
    }

    private function updateNumberOfNeutralGarrisonUnits()
    {
        $this->_numberOfNeutralGarrisonUnits = floor($this->_turnNumber / 10) + 1;
        if ($this->_numberOfNeutralGarrisonUnits > 4) {
            $this->_numberOfNeutralGarrisonUnits = 4;
        }
    }

    private function updateNumberOfComputerArmyUnits()
    {
        $this->_numberOfComputerArmyUnits = floor($this->_turnNumber / 7);
    }

    public function getNumberOfGarrisonUnits()
    {
        return $this->_numberOfGarrisonUnits;
    }

    public function getNumberOfNeutralGarrisonUnits()
    {
        return $this->_numberOfNeutralGarrisonUnits;
    }

    public function getNumberOfComputerArmyUnits()
    {
        return $this->_numberOfComputerArmyUnits;
    }

    public function updateChatHistory($message, $color)
    {
        $this->_chatHistory[] = array('date' => date('Y-m-d H:i:s', time()), 'message' => $message, 'color' => $color);
    }

    public function addUser($playerId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        $this->_users[$playerId] = $user;
        $this->updateOnline($this->getPlayerColor($playerId), 1);
    }

    public function removeUser($playerId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
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

    public function getType()
    {
        return $this->_type;
    }
}