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
    private $_turnPlayerId;

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
        $this->_l = new Coret_Model_Logger();

        $this->_id = $gameId;

        $mGame = new Application_Model_Game($this->_id, $db);
        $game = $mGame->getGame();

        $this->_mapId = $game['mapId'];
        $this->_begin = $game['begin'];
        $this->_turnsLimit = $game['turnsLimit'];
        $this->_turnTimeLimit = $game['turnTimeLimit'];
        $this->_timeLimit = $game['timeLimit'];

        $this->setTurnPlayerId($game['turnPlayerId']);

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
            $this->getPlayerColor($playerId),
            $this->_players[$this->getPlayerColor($playerId)]->getTeam(),
            $mPlayersInGame->getMe($playerId),
            $battleSequence
        );
        if ($this->_turnPlayerId == $playerId) {
            $this->_me->setTurn(true);
        }

        $this->initFields();
    }

    private function initFields()
    {
        foreach ($this->_players as $color => $player) {
            foreach ($player->armiesToArray() as $armyId => $army) {
                $this->_fields->initArmy($army['x'], $army['y'], $armyId, $color);
            }
            foreach ($player->castlesToArray() as $castleId => $castle) {
                $this->_fields->initCastle($castle['x'], $castle['y'], $castleId, $color);
            }
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
            $this->_fields->initCastle($castle['x'], $castle['y'], $castleId, 'neutral');
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
            $this->_fields->initRuin($position['x'], $position['y'], $ruinId, $empty);
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
            'fields' => $this->_fields->toArray(),
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
        if (isset($this->_players[$this->getPlayerColor($playerId)])) {
            return $this->_players[$this->getPlayerColor($playerId)]->hasCastle($castleId);
        }
    }

    public function canCastleProduceThisUnit($playerId, $castleId, $unitId)
    {
        return $this->_players[$this->getPlayerColor($playerId)]->canCastleProduceThisUnit($castleId, $unitId);
    }

    public function getCastleCurrentProductionId($playerId, $castleId)
    {
        return $this->_players[$this->getPlayerColor($playerId)]->getCastleCurrentProductionId($castleId);
    }

    public function setProductionId($playerId, $castleId, $unitId, $relocationToCastleId, $db)
    {
        $this->_players[$this->getPlayerColor($playerId)]->setProduction($this->_id, $castleId, $unitId, $relocationToCastleId, $db);
    }

    public function setBattleSequence($battleSequence)
    {
        $this->_me->setBattleSequence($battleSequence);
    }

    public function getPlayerArmy($playerId, $armyId)
    {
        return $this->_players[$this->getPlayerColor($playerId)]->getArmy($armyId);
    }

    public function getFields()
    {
        return $this->_fields;
    }

    public function isOtherArmyAtPosition($playerId, $armyId)
    {
        return $this->_players[$this->getPlayerColor($playerId)]->isOtherArmyAtPosition($armyId);
    }

    public function joinArmiesAtPosition($playerId, $armyId, $db)
    {
        return $this->_players[$this->getPlayerColor($playerId)]->joinArmiesAtPosition($armyId, $this->_id, $db);
    }

    public function isTowerAtField($x, $y)
    {
        return $this->_fields->isTower($x, $y);
    }

    public function isMyCastleAtField($x, $y)
    {
        return $this->_fields->isMyCastle($x, $y);
    }

    public function isPlayerCastleAtField($playerId, $x, $y)
    {
        return $this->_fields->isPlayerCastle($this->getPlayerColor($playerId), $x, $y);
    }

    public function isArmyAtField($x, $y)
    {
        return $this->_fields->isArmy($x, $y);
    }

    public function isTowerAtFieldOpen($x, $y)
    {
        return $this->_fields->isTowerOpen($x, $y, $this->_me->getColor(), $this->_me->getTeam());
    }

    public function noPlayerArmiesAndCastles($playerId)
    {
        return $this->_players[$this->getPlayerColor($playerId)]->noCastlesExists() && $this->_players[$this->getPlayerColor($playerId)]->noArmiesExists();
    }

    public function getPlayerColor($playerId)
    {
        return $this->_playersInGameColors[$playerId];
    }

    public function getPlayerTeam($playerId)
    {
        return $this->_players[$this->getPlayerColor($playerId)]->getTeam();
    }

    public function setPlayerLost($playerId, $db)
    {
        $this->_players[$this->getPlayerColor($playerId)]->setLost(true);
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);
        $mPlayersInGame->setPlayerLostGame($playerId);
    }

    public function allEnemiesAreDead($playerId)
    {
        $playerColor = $this->getPlayerColor($playerId);
        $playerTeam = $this->getPlayerTeam($playerId);
        foreach ($this->_players as $color => $player) {
            if ($color == $playerColor) {
                continue;
            }
            if ($playerTeam == $player->getTeam()) {
                continue;
            }

            if ($this->_players[$color]->castlesExists() || $this->_players[$color]->armiesExists()) {
                return false;
            }
        }

        return true;
    }

    public function playerArmiesOrCastlesExists($playerId)
    {
        $color = $this->getPlayerColor($playerId);
        return $this->_players[$color]->armiesExists() || $this->_players[$color]->castlesExists();
    }

    public function getExpectedNextTurnPlayer($playerId, $db)
    {
        $playerColor = $this->getPlayerColor($playerId);
        $find = false;
        reset($this->_playersInGameColors);
        $firstColor = current($this->_playersInGameColors);

        /* szukam następnego koloru w dostępnych kolorach */
        foreach ($this->_playersInGameColors as $color) {
            /* znajduję kolor gracza, który ma aktualnie turę i przewijam na następny */
            if ($playerColor == $color) {
                $find = true;
                continue;
            }

            /* to jest przewinięty kolor gracza */
            if ($find) {
                $nextPlayerColor = $color;
                break;
            }
        }

        /* jeśli nie znalazłem następnego gracza to następnym graczem jest gracz pierwszy */
        if (!isset($nextPlayerColor)) {
            $nextPlayerColor = $firstColor;
        }

        /* przypisuję playerId do koloru */
        $player = $this->_players[$nextPlayerColor];
        $color = $player->getColor();
        if ($color == $firstColor) {
            $this->turnNumberIncrement();
            $mGame = new Application_Model_Game($this->_id, $db);
            $mGame->updateTurnNumber($player->getId(), $this->_turnNumber);
        }
        return $player->getId();
    }

    private function turnNumberIncrement()
    {
        $this->_turnNumber++;
    }

    public function increaseAllCastlesProductionTurn($playerId, $db)
    {
        $mCastlesInGame = new Application_Model_CastlesInGame($this->_id, $db);
        $mCastlesInGame->increaseAllCastlesProductionTurn($playerId);
    }

    public function getPlayerGold($playerId)
    {
        return $this->_players[$this->getPlayerColor($playerId)]->getGold();
    }

    public function activatePlayerTurn($playerId, $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);
        $mPlayersInGame->turnActivate($playerId);

        $playerColor = $this->getPlayerColor($playerId);
        foreach ($this->_players as $color => $player) {
            if ($playerColor == $color) {
                $player->setTurnActive(true);
            } else {
                $player->setTurnActive(false);
            }
        }
    }

    public function startPlayerTurn($playerId, $db)
    {
        $this->_players[$this->getPlayerColor($playerId)]->startTurn($this->getTurnNumber(), $db);
    }

    public function addTower($playerId, $towerId, $color, $db)
    {
        $mTowersInGame = new Application_Model_TowersInGame($this->_id, $db);

        if ($color == 'neutral') {
            $this->_players[$this->getPlayerColor($playerId)]->addTower($towerId, $this->_neutralTowers[$towerId]);
            unset($this->_neutralTowers[$towerId]);
            $mTowersInGame->addTower($towerId, $playerId);
        } else {
            $this->_players[$this->getPlayerColor($playerId)]->addTower($towerId, $this->_players[$color]->getTower($towerId));
            $this->_players[$color]->removeTower($towerId);
            $mTowersInGame->changeTowerOwner($towerId, $playerId);
        }
    }

    public function unfortifyPlayerArmies($playerId, $db)
    {
        $mArmy = new Application_Model_Army($this->_id, $db);
        $mArmy->unfortifyPlayerArmies($playerId);
        $this->_players[$this->getPlayerColor($playerId)]->unfortifyArmies();
    }

    public function setTurnPlayerId($playerId)
    {
        $this->_turnPlayerId = $playerId;
    }

    public function getTurnPlayerId()
    {
        return $this->_turnPlayerId;
    }

    public function getPlayerTurnActive($playerId)
    {
        return $this->_players[$this->getPlayerColor($playerId)]->getTurnActive();
    }

    public function isComputer($playerId)
    {
        return $this->_players[$this->getPlayerColor($playerId)]->getComputer();
    }

    public function getComputerArmyToMove($playerId)
    {
        return $this->_players[$this->getPlayerColor($playerId)]->getComputerArmyToMove();
    }

    /*
     * **************************
     * *** COMPUTER FUNCTIONS ***
     * **************************
     */

    /**
     * @param $playerId
     * @param $computer
     */
    public function getComputerEmptyCastleInComputerRange($playerId, $computer)
    {
        $this->_l->logMethodName();

        $this->_players[$this->getPlayerColor($playerId)]->getComputerEmptyCastleInComputerRange($computer, $this->_fields);
    }

    public function getArmiesFromCastle($playerId, $castle)
    {

    }

    public function getEnemiesHaveRangeAtThisCastle($playerId, $castle)
    {
        $this->_l->logMethodName();
        $enemiesHaveRange = array();
        $playerColor = $this->getPlayerColor($playerId);
        $castleX = $castle->getX();
        $castleY = $castle->getY();


        foreach ($this->_players as $color => $player) {
            if ($playerColor == $color) {
                continue;
            }
            if ($this->sameTeam($color)) {
                continue;
            }
            foreach ($player->getArmies() as $enemy) {
                $mHeuristics = new Cli_Model_Heuristics($castleX, $castleY);
                $h = $mHeuristics->calculateH($enemy->x, $enemy->y);
                if ($h < 20) {
                    $this->_fields->setCastleTemporaryType($castleX, $castleY, 'E');
                    try {
                        $aStar = new Cli_Model_Astar($enemy, $castleX, $castleY, $this->_fields, $playerColor);
                    } catch (Exception $e) {
                        echo($e);
                        return;
                    }

                    $this->_fields->resetTemporaryType($castleX, $castleY);

                    if ($enemy->unitsHaveRange($aStar->getPath($castleX . '_' . $castleY, $playerColor))) {
                        $enemiesHaveRange[] = $enemy;
                    }
                }
            }
        }

        return $enemiesHaveRange;
    }
}