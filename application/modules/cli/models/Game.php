<?php

class Cli_Model_Game
{
    private $_id;

    private $_mapId;

    private $_turnNumber = 1;

    private $_capitals = array();
//    private $_teams = array();
    private $_playersInGameColors;
    private $_online = array();

    private $_begin;
    private $_turnsLimit;
    private $_turnTimeLimit;
    private $_timeLimit;

    private $_turnHistory;

    private $_me;

    private $_fields;
    private $_terrain;
    private $_units;
    private $_specialUnits = array();
    private $_firstUnitId;

    private $_neutralCastles = array();
    private $_players = array();
    private $_ruins = array();
    private $_neutralTowers = array();

    private $_statistics;

    public function __construct($playerId, $gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_id = $gameId;

        $mGame = new Application_Model_Game($this->_id, $db);
        $game = $mGame->getGame();

        $this->_mapId = $game['mapId'];
        $this->_begin = $game['begin'];
        $this->_turnsLimit = $game['turnsLimit'];
        $this->_turnTimeLimit = $game['turnTimeLimit'];
        $this->_timeLimit = $game['timeLimit'];

        $mTurnHistory = new Application_Model_TurnHistory($this->_id, $db);
        $this->_turnHistory = $mTurnHistory->getTurnHistory();

        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);
//        $this->_teams = $mPlayersInGame->getTeams();
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
        $this->_fields = $mMapFields->getMapFields();

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

        $mMapCastles = new Application_Model_MapCastles($this->_mapId, $db);
        $mapCastles = $mMapCastles->getMapCastles();
        $this->initNeutralCastles($mapCastles, $db);
        $this->initNeutralTowers($db);
        $this->initPlayers($mapCastles, $mMapPlayers, $db);
        $this->initRuins($db);

        $mBattleSequence = new Application_Model_BattleSequence($this->_id, $db);
        $battleSequence = $mBattleSequence->get($playerId);
        if (empty($battleSequence)) {
            $mBattleSequence->initiate($playerId, $this->_units);
            $battleSequence = $mBattleSequence->get($playerId);
        }
        $this->_me = new Cli_Model_Me(
            $this->_playersInGameColors[$playerId],
            $this->_players[$this->_playersInGameColors[$playerId]]->getTeam(),
            $mPlayersInGame->getMe($playerId),
            $battleSequence
        );
        if ($game['turnPlayerId'] == $playerId) {
            $this->_me->setTurn(true);
        }

        $this->initFields();
    }

    private function initFields()
    {
        foreach ($this->_players as $color => $player) {
            if ($this->_me->isMyColor($color)) {
                foreach ($player->armiesToArray() as $armyId => $army) {
                    if ($this->_fields[$army['y']][$army['x']] == 'w' && $army->canSwim()) {
                        $this->_fields[$army['y']][$army['x']] = 'su' . $armyId;
                    } else {
                        $this->_fields[$army['y']][$army['x']] = 'ma' . $armyId;
                    }
                }
                foreach ($player->castlesToArray() as $castleId => $castle) {
                    $this->_fields[$castle['y']][$castle['x']] = 'mc' . $castleId;
                    $this->_fields[$castle['y'] + 1][$castle['x']] = 'mc' . $castleId;
                    $this->_fields[$castle['y']][$castle['x'] + 1] = 'mc' . $castleId;
                    $this->_fields[$castle['y'] + 1][$castle['x'] + 1] = 'mc' . $castleId;
                }
                continue;
            }
            if ($player->getTeam() == $this->_me->getTeam()) {
                foreach ($player->castlesToArray() as $castleId => $castle) {
                    $this->_fields[$castle['y']][$castle['x']] = 'tc' . $castleId;
                    $this->_fields[$castle['y'] + 1][$castle['x']] = 'tc' . $castleId;
                    $this->_fields[$castle['y']][$castle['x'] + 1] = 'tc' . $castleId;
                    $this->_fields[$castle['y'] + 1][$castle['x'] + 1] = 'tc' . $castleId;
                }
                continue;
            }
            foreach ($player->armiesToArray() as $armyId => $army) {
                $this->_fields[$army['y']][$army['x']] = 'ea' . $armyId;
            }
            foreach ($player->castlesToArray() as $castleId => $castle) {
                $this->_fields[$castle['y']][$castle['x']] = 'ec' . $castleId;
                $this->_fields[$castle['y'] + 1][$castle['x']] = 'ec' . $castleId;
                $this->_fields[$castle['y']][$castle['x'] + 1] = 'ec' . $castleId;
                $this->_fields[$castle['y'] + 1][$castle['x'] + 1] = 'ec' . $castleId;
            }
        }
        foreach ($this->_neutralCastles as $castleId => $castle) {
            $this->_fields[$castle['y']][$castle['x']] = 'nc' . $castleId;
            $this->_fields[$castle['y'] + 1][$castle['x']] = 'nc' . $castleId;
            $this->_fields[$castle['y']][$castle['x'] + 1] = 'nc' . $castleId;
            $this->_fields[$castle['y'] + 1][$castle['x'] + 1] = 'nc' . $castleId;
        }
    }

    private function initNeutralCastles($mapCastles, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mCastlesInGame = new Application_Model_CastlesInGame($this->_id, $db);
        $playersCastles = $mCastlesInGame->getAllCastles();

        foreach ($mapCastles as $castleId => $castle) {
            if (isset($playersCastles[$castleId])) {
                continue;
            }
            $this->_neutralCastles[$castleId] = $castle;
        }
    }

    private function initPlayers($mapCastles, Application_Model_MapPlayers $mMapPlayers, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);
        $players = $mPlayersInGame->getGamePlayers();

        foreach ($this->_playersInGameColors as $playerId => $color) {
            $this->_players[$color] = new Cli_Model_Player($players[$playerId], $this->_id, $mapCastles, $mMapPlayers, $db);
        }
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

    private function initNeutralTowers(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mTowersInGame = new Application_Model_TowersInGame($this->_id, $db);
        $playersTowers = $mTowersInGame->getTowers();

        $mMapTowers = new Application_Model_MapTowers($this->_mapId, $db);
        foreach ($mMapTowers->getMapTowers() as $towerId => $tower) {
            if (isset($playersTowers[$towerId])) {
                continue;
            }
            $this->_neutralTowers[$towerId] = $tower;
        }
    }

    private function playersToArray()
    {
        $players = array();
        foreach ($this->_players as $color => $player) {
            $players[$color] = $player->toArray();
        }
        return $players;
    }

    public function updatePlayerArmy($army, $color)
    {
        $this->_players[$color]->updateArmy($army);
    }

    public function updatePlayerCastle($castle, $color)
    {
        $this->_players[$color]->updateCastle($castle);
    }

    public function updatePlayerTower($tower, $color)
    {
        $this->_players[$color]->updateTower($tower);
    }

    private function ruinsToArray()
    {
        $ruins = array();
        foreach ($this->_ruins as $ruinId => $ruin) {
            $ruins[$ruinId] = $ruin->toArray();
        }
        return $ruins;
    }

    public function emptyRuin($ruinId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mRuinsInGame = new Application_Model_RuinsInGame($this->_id, $db);
        if ($mRuinsInGame->add($ruinId)) {
            $this->_ruins[$ruinId]->empty = true;
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
            'fields' => $this->_fields,
            'terrain' => $this->_terrain,
            'capitals' => $this->_capitals,
            'playersInGameColors' => $this->_playersInGameColors,
//            'teams' => $this->_teams,
            'online' => $this->_online,
            'chatHistory' => $this->_chatHistory,
            'turnHistory' => $this->_turnHistory,
            'me' => $this->_me->toArray(),
            'players' => $this->playersToArray(),
            'ruins' => $this->ruinsToArray(),
            'neutralCastles' => $this->_neutralCastles,
            'neutralTowers' => $this->_neutralTowers
        );
    }

    public function isPlayerCastle($playerId, $castleId)
    {
        if (isset($this->_players[$this->_playersInGameColors[$playerId]])) {
            return $this->_players[$this->_playersInGameColors[$playerId]]->hasCastle($castleId);
        }
    }

    public function canCastleProduceThisUnit($playerId, $castleId, $unitId)
    {
        return $this->_players[$this->_playersInGameColors[$playerId]]->canCastleProduceThisUnit($castleId, $unitId);
    }

    public function getCastleCurrentProductionId($playerId, $castleId)
    {
        return $this->_players[$this->_playersInGameColors[$playerId]]->getCastleCurrentProductionId($castleId);
    }

    public function setProductionId($playerId, $castleId, $unitId, $relocationToCastleId, $db)
    {
        $this->_players[$this->_playersInGameColors[$playerId]]->setProduction($this->_id, $castleId, $unitId, $relocationToCastleId, $db);
    }

    public function setBattleSequence($battleSequence)
    {
        $this->_me->setBattleSequence($battleSequence);
    }

    public function getPlayerArmy($playerId, $armyId)
    {
        return $this->_players[$this->_playersInGameColors[$playerId]]->getArmy($armyId);
    }

    public function getFields()
    {
        return $this->_fields;
    }

    public function isOtherArmyAtPosition($playerId, $armyId)
    {
        return $this->_players[$this->_playersInGameColors[$playerId]]->isOtherArmyAtPosition($armyId);
    }
}