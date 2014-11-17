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
            if ($color == $playerColor || $playerTeam == $player->getTeam()) {
                continue;
            }
            if ($this->_players[$color]->castlesExists() || $this->_players[$color]->armiesExists()) {
                return false;
            }
        }
        return true;
    }

    public function noEnemyCastles($playerId)
    {
        $playerColor = $this->getPlayerColor($playerId);
        $playerTeam = $this->getPlayerTeam($playerId);
        foreach ($this->_players as $color => $player) {
            if ($color == $playerColor || $playerTeam == $player->getTeam()) {
                continue;
            }
            if ($this->_players[$color]->castlesExists()) {
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

    public function getPlayerCastle($playerId, $castleId)
    {
        return $this->_players[$this->getPlayerColor($playerId)]->getCastle($castleId);
    }

    public function sameTeam($color1, $color2)
    {
        if ($color1 == $color2) {
            return true;
        }
        return $this->_players[$color1]->getTeam() == $this->_players[$color2]->getTeam();
    }

    public function searchRuin($ruinId, $army, $playerId, $db)
    {
        $random = rand(0, 100);
        $heroId = $army->getAnyHeroId();

        if ($random < 10) { //10%
//śmierć
            if ($this->_turnNumber <= 7) {
                $army->zeroHeroMovesLeft($heroId, $this->_id, $db);
                $found = array('null', 1);
            } else {
                $found = array('death', 1);
                $army->killHero($heroId, $playerId, $this->_id, $db);
            }
        } elseif ($random < 55) { //45%
//kasa
            $gold = rand(50, 150);
            $found = array('gold', $gold);
            $this->_players[$this->getPlayerColor($playerId)]->addGold($gold, $this->_id, $db);
            $army->zeroHeroMovesLeft($heroId, $this->_id, $db);
            $this->_ruins[$ruinId]->setEmpty($this->_id, $db);
        } elseif ($random < 85) { //30%
//jednostki
            if ($this->_turnNumber <= 9) {
                $min1 = 1;
                $max1 = 1;
                $min2 = 1;
                $max2 = 1;
            } elseif ($this->_turnNumber <= 13) {
                $min1 = 0;
                $max1 = 1;
                $min2 = 1;
                $max2 = 1;
            } elseif ($this->_turnNumber <= 17) {
                $min1 = 0;
                $max1 = 2;
                $min2 = 1;
                $max2 = 1;
            } elseif ($this->_turnNumber <= 21) {
                $min1 = 0;
                $max1 = 3;
                $min2 = 1;
                $max2 = 1;
            } elseif ($this->_turnNumber <= 25) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 1;
                $max2 = 1;
            } elseif ($this->_turnNumber <= 32) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 1;
                $max2 = 2;
            } elseif ($this->_turnNumber <= 39) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 1;
                $max2 = 3;
            } elseif ($this->_turnNumber <= 46) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 2;
                $max2 = 3;
            } elseif ($this->_turnNumber <= 53) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 3;
                $max2 = 3;
            } elseif ($this->_turnNumber <= 60) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 4;
                $max2 = 4;
            } elseif ($this->_turnNumber <= 67) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 5;
                $max2 = 5;
            } elseif ($this->_turnNumber <= 74) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 6;
                $max2 = 6;
            } elseif ($this->_turnNumber <= 81) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 7;
                $max2 = 7;
            } elseif ($this->_turnNumber <= 88) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 8;
                $max2 = 8;
            } elseif ($this->_turnNumber <= 95) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 9;
                $max2 = 9;
            } elseif ($this->_turnNumber <= 102) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 10;
                $max2 = 10;
            } else {
                $min1 = 0;
                $max1 = 4;
                $min2 = 11;
                $max2 = 11;
            }

            $unitId = $this->_specialUnits[rand($min1, $max1)]['unitId'];
            $numberOfUnits = rand($min2, $max2);

            for ($i = 0; $i < $numberOfUnits; $i++) {
                $army->createSoldier($this->_id, $playerId, $unitId, $db);
            }

            $army->zeroHeroMovesLeft($heroId, $this->_id, $db);
            $this->_ruins[$ruinId]->setEmpty($this->_id, $db);
            $found = array('allies', $numberOfUnits);
//        } elseif ($random < 95) { //10%
        } else {
//nic
            $army->zeroHeroMovesLeft($heroId, $this->_id, $db);
            $found = array('null', 1);

//        } else { //5%
////artefakt
//            $artifactId = rand(5, 34);
//
//            $mChest = new Application_Model_Chest($playerId, $db);
//
//            if ($mChest->artifactExists($artifactId)) {
//                $mChest->increaseArtifactQuantity($artifactId);
//            } else {
//                $mChest->add($artifactId);
//            }
//
//            $found = array('artifact', $artifactId);
//
//            Cli_Model_Database::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
//
//            $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
//            $mRuinsInGame->add($ruinId);
//
        }
        return $found;
    }


    public function getNeutralCastleGarrison()
    {
        $numberOfSoldiers = ceil($this->_turnNumber / 10);
        $soldiers = array();
        for ($i = 1; $i <= $numberOfSoldiers; $i++) {
            $soldiers[] = array(
                'defensePoints' => 3,
                'soldierId' => 's' . $i,
                'unitId' => $this->_firstUnitId
            );
        }

        $army = new Cli_Model_Army(array(
            'id' => 0,
            'x' => 0,
            'y' => 0
        ));
        $army->addSoldiers($soldiers);

        return $army;
    }

    public function getCastleGarrison($playerId, $castleId)
    {
        return $this->_players[$this->getPlayerColor($playerId)]->getCastleGarrison($castleId);
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
            if ($this->sameTeam($playerColor, $color)) {
                continue;
            }
            foreach ($player->getArmies() as $enemy) {
                $mHeuristics = new Cli_Model_Heuristics($castleX, $castleY);
                $h = $mHeuristics->calculateH($enemy->getX(), $enemy->getY());
                if ($h < $enemy->getMovesLeft()) {
                    $this->_fields->setCastleTemporaryType($castleX, $castleY, 'E');
                    try {
                        $aStar = new Cli_Model_Astar($enemy, $castleX, $castleY, $this->_fields, $playerColor);
                    } catch (Exception $e) {
                        echo($e);
                        return;
                    }

                    $this->_fields->resetCastleTemporaryType($castleX, $castleY);

                    if ($enemy->unitsHaveRange($aStar->getPath($castleX . '_' . $castleY, $playerColor))) {
                        $enemiesHaveRange[] = $enemy;
                    }
                }
            }
        }

        return $enemiesHaveRange;
    }

    public function getEnemiesInRange($playerId, $army)
    {
        $this->_l->logMethodName();
        $enemiesInRange = array();
        $playerColor = $this->getPlayerColor($playerId);

        foreach ($this->_players as $color => $player) {
            if ($this->sameTeam($playerColor, $color)) {
                continue;
            }
            foreach ($player->getArmies() as $enemy) {
                $enemyX = $enemy->getX();
                $enemyY = $enemy->getY();
                $mHeuristics = new Cli_Model_Heuristics($army->getX(), $army->getY());
                $h = $mHeuristics->calculateH($enemyX, $enemyY);
                if ($h < $army->getMovesLeft()) {
                    $this->_fields->setTemporaryType($enemyX, $enemyY, 'E');
                    try {
                        $aStar = new Cli_Model_Astar($army, $enemyX, $enemyY, $this->_fields, $playerColor);
                    } catch (Exception $e) {
                        echo($e);
                        return;
                    }

                    $this->_fields->resetTemporaryType($enemyX, $enemyY);

                    $move = $army->calculateMovesSpend($aStar->getPath($enemyX . '_' . $enemyY, $playerColor));
                    if ($move->x == $enemyX && $move->y == $enemyY) {
                        $enemiesInRange[] = $enemy;
                    }
                }
            }
        }
        if (!empty($enemiesInRange)) {
            return $enemiesInRange;
        } else {
//             new Game_Logger('BRAK WROGA W ZASIĘGU ARMII');
            return false;
        }
    }

    public function getPathToNearestRuin($playerId, $army)
    {
        $this->_l->logMethodName();
        $armyX = $army->getX();
        $armyY = $army->getY();
        $movesLeft = $army->getMovesLeft();
        $playerColor = $this->getPlayerColor($playerId);

        foreach ($this->_ruins as $ruinId => $ruin) {
            if ($ruin->getEmpty()) {
                continue;
            }

            $ruinX = $ruin->getX();
            $ruinY = $ruin->getY();

            $mHeuristics = new Cli_Model_Heuristics($armyX, $armyY);
            $h = $mHeuristics->calculateH($ruinX, $ruinY);
            if ($h < $movesLeft) {
                try {
                    $aStar = new Cli_Model_Astar($army, $ruinX, $ruinY, $this->_fields, $playerColor);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }
                $path = $army->calculateMovesSpend($aStar->getPath($ruinX . '_' . $ruinY, $playerColor));
                if ($path->x == $ruinX && $path->y == $ruinY) {
                    $path->ruinId = $ruinId;
                    return $path;
                }
            }
        }
    }

    public function getWeakerHostileCastle($playerId, $army, $castlesIds = array())
    {
        $this->_l->logMethodName();
        $playerColor = $this->getPlayerColor($playerId);
        $heuristics = array();
        $armyX = $army->getX();
        $armyY = $army->getY();

        foreach ($this->_neutralCastles as $castleId => $castle) {
            if (in_array($castleId, $castlesIds)) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($castle['x'], $castle['y']);
            $heuristics[$castleId] = $mHeuristics->calculateH($armyX, $armyY);
        }

        foreach ($this->_players as $color => $player) {
            if ($this->sameTeam($playerColor, $color)) {
                continue;
            }
            foreach ($player->getCastles() as $castleId => $castle) {
                if (in_array($castleId, $castlesIds)) {
                    continue;
                }
                $mHeuristics = new Cli_Model_Heuristics($castle->getX(), $castle->getY());
                $heuristics[$castleId] = $mHeuristics->calculateH($armyX, $armyY);
            }
        }

        asort($heuristics, SORT_NUMERIC);

        foreach (array_keys($heuristics) as $id) {
            if (isset($this->_neutralCastles[$id])) {
                $enemy = $this->getNeutralCastleGarrison();
            } else {
                $enemy = $this->getCastleGarrison($playerId, $id);
            }

            if (!$this->isEnemyStronger(new Cli_Model_Army($enemy), $id)) {
                return $id;
            }
        }
        return null;
    }

    public function findNearestWeakestHostileCastle($playerId, $army)
    {
        $this->_l->logMethodName();
        $omittedCastlesIds = array();
        $weakerHostileCastleId = $this->getWeakerHostileCastle($playerId, $army);

        if (!$weakerHostileCastleId) {
            return new Cli_Model_Path();
        }

        $path = $this->getPathToEnemyCastleInRange($weakerHostileCastleId);
        while (true) {
            if (!isset($path->current) || empty($path->current)) {
                $omittedCastlesIds[] = $weakerHostileCastleId;
                $weakerHostileCastleId = $this->getWeakerHostileCastle($playerId, $army, $omittedCastlesIds);
                if ($weakerHostileCastleId) {
                    $path = $this->getPathToEnemyCastleInRange($weakerHostileCastleId);
                } else {
                    break;
                }
            }
            break;
        }
        $path->castleId = $weakerHostileCastleId;
        return $path;
    }

    public function getPathToMyCastle($playerId, $army, $castle)
    {
        $this->_l->logMethodName();
        $castleX = $castle->getX();
        $castleY = $castle->getY();
        $color = $this->getPlayerColor($playerId);

        if ($castleX == $army->getX() && $castleY == $army->getY()) {
            return;
        }
        try {
            $aStar = new Cli_Model_Astar($army, $castleX, $castleY, $this->_fields, $color);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        return $army->calculateMovesSpend($aStar->getPath($castleX . '_' . $castleY, $color));
    }

    /**
     * @param $playerId
     * @return mixed
     *
     * todo sprawdzić jak to działa
     */
    public function getMyCastleNearEnemy($playerId)
    {
        $this->_l->logMethodName();
        $playerColor = $this->getPlayerColor($playerId);
        $castlesHeuristics = array();

        foreach ($this->_players[$playerColor]->getCastles() as $castleId => $castle) {
            $mHeuristics = new Cli_Model_Heuristics($castle->getX(), $castle->getY());
            foreach ($this->_players as $color => $player) {
                if ($this->sameTeam($playerColor, $color)) {
                    continue;
                }

                foreach ($this->_players[$color]->getArmies() as $enemy) {
                    if (isset($castlesHeuristics[$castleId])) { // co tu się dzieje?
                        $castlesHeuristics[$castleId] += $mHeuristics->calculateH($enemy->getX(), $enemy->getY());
                    } else {
                        $castlesHeuristics[$castleId] = $mHeuristics->calculateH($enemy->getX(), $enemy->getY());
                    }
                }
            }
        }

        if (empty($castlesHeuristics)) {
            return;
        }

        asort($castlesHeuristics, SORT_NUMERIC);
        reset($castlesHeuristics);
        return $this->_players[$playerColor]->getCastle(key($castlesHeuristics));
    }

    protected function getPathToMyArmyInRange($playerId, $army)
    {
        $this->_l->logMethodName();
        if ($this->_turnNumber < 5) {
            return;
        }
        $playerColor = $this->getPlayerColor($playerId);
        $myArmies = array();
        $myArmyId = $army->getId();

        $numberOfUnits = floor($this->_turnNumber / 7);
        if ($numberOfUnits > 4) {
            $numberOfUnits = 4;
        }
        $numberOfSoldiersAndHeroes = count($army->getNumberOfSoldiers()) + count($army->getNumberOfHeroes());
        foreach ($this->_players[$playerColor]->getArmies() as $armyId => $army) {
            if ($armyId == $myArmyId) {
                continue;
            }
            $myArmies[$armyId] = $army;
        }

        foreach ($myArmies as $armyId => $army) {
            $numberOfSoldiers = $army->getNumberOfSoldiers();
            $armyX = $army->getX();
            $armyY = $army->getY();

            if ($this->_fields->isPlayerCastle($playerColor, $armyX, $armyY)) {
                if ($numberOfUnits == $numberOfSoldiers) {
                    continue;
                }
            }

            if ($numberOfSoldiersAndHeroes > 3 * $numberOfSoldiers) {
                continue;
            }

            if ($numberOfSoldiers > 3 * $numberOfSoldiersAndHeroes) {
                continue;
            }

            $mHeuristics = new Cli_Model_Heuristics($armyX, $armyY);
            $h = $mHeuristics->calculateH($armyX, $armyY);
            if ($h < $army->getMovesLeft()) {
                try {
                    $aStar = new Cli_Model_Astar($army, $armyX, $armyY, $this->_fields, $playerColor);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $army->calculateMovesSpend($aStar->getPath($armyX . '_' . $armyY, $playerColor));
                if ($move->x == $armyX && $move->y == $armyY) {
                    return $move;
                }
            }
        }
        return;
    }

    public function getStrongerEnemyArmyInRange($playerId, $army)
    {
        $this->_l->logMethodName();
        $playerColor = $this->getPlayerColor($playerId);
        $armyX = $army->getX();
        $armyY = $army->getY();
        $movesLeft = $army->getMovesLeft();

        foreach ($this->_players as $color => $player) {
            if ($this->sameTeam($playerColor, $color)) {
                continue;
            }
            foreach ($player->getArmies() as $enemy) {
                $enemyX = $enemy->getX();
                $enemyY = $enemy->getY();

                $mHeuristics = new Cli_Model_Heuristics($enemyX, $enemyY);
                $h = $mHeuristics->calculateH($armyX, $armyY);
                if ($h < $movesLeft) {
                    $castleId = $this->_fields->isPlayerCastle($color, $enemyX, $enemyY);

                    if (!$this->isEnemyStronger($enemy, $castleId)) {
                        continue;
                    }
                    if ($castleId) {
                        $this->_fields->setCastleTemporaryType($enemyX, $enemyY, 'E');
                    } else {
                        $this->_fields->setTemporaryType($enemyX, $enemyY, 'E');
                    }
                    try {
                        $aStar = new Cli_Model_Astar($army, $enemyX, $enemyY, $this->_fields, $playerColor);
                    } catch (Exception $e) {
                        echo($e);
                        return;
                    }

                    $move = $army->calculateMovesSpend($aStar->getPath($enemyX . '_' . $enemyY, $playerColor));
                    if ($castleId) {
                        $this->_fields->resetCastleTemporaryType($enemyX, $enemyY);
                    } else {
                        $this->_fields->resetTemporaryType($enemyX, $enemyY);
                    }
                    if ($move->x == $enemyX && $move->y == $enemyY) {
                        return $enemy->getId();
                    }
                }
            }
        }
        return null;
    }

    public function getWeakerEnemyArmyInRange($playerId, $army)
    {
        $this->_l->logMethodName();
        $playerColor = $this->getPlayerColor($playerId);
        $armyX = $army->getX();
        $armyY = $army->getY();
        $movesLeft = $army->getMovesLeft();

        foreach ($this->_enemies as $enemyId => $enemy) {
            $enemyX = $enemy->getX();
            $enemyY = $enemy->getY();
            $mHeuristics = new Cli_Model_Heuristics($enemyX, $enemyY);
            $h = $mHeuristics->calculateH($armyX, $armyY);
            if ($h < $movesLeft) {
                $castleId = $this->_fields->isNotPlayerCastle($playerColor, $enemyX, $enemyY);
                if ($this->isEnemyStronger($enemy, $castleId)) {
                    continue;
                }
                if ($castleId) {
                    $this->_fields->setCastleTemporaryType($enemyX, $enemyY, 'E');
                } else {
                    $this->_fields->setTemporaryType($enemyX, $enemyY, 'E');
                }
                try {
                    $aStar = new Cli_Model_Astar($army, $enemyX, $enemyY, $this->_fields, $playerColor);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $army->calculateMovesSpend($aStar->getPath($enemyX . '_' . $enemyY, $playerColor));
                if ($castleId) {
                    $this->_fields->resetCastleTemporaryType($enemyX, $enemyY);
                } else {
                    $this->_fields->resetTemporaryType($enemyX, $enemyY);
                }
                if ($move->x == $enemyX && $move->y == $enemyY) {
                    $move->castleId = $castleId;
                    $move->armyId = $enemyId;
                    return $move;
                }
            }
        }

        return;
    }

    public function canAttackAllEnemyHaveRange($playerId, $army, $enemiesHaveRange)
    {
        $this->_l->logMethodName();
        $playerColor = $this->getPlayerColor($playerId);
        foreach ($enemiesHaveRange as $enemy) {
            $castleId = $this->_fields->isPlayerCastle($enemy->getX(), $enemy->getY(), $playerColor);
            if ($this->isEnemyStronger($enemy, $castleId)) {
                return;
            }
        }
        return $enemy;
    }

    public function isMyCastleInRangeOfEnemy($playerId, $pathToMyEmptyCastle)
    {
        $this->_l->logMethodName();
        $playerColor = $this->getPlayerColor($playerId);

        foreach ($this->_enemies as $enemy) {
            $enemyX = $enemy->getX();
            $enemyY = $enemy->getY();
            $mHeuristics = new Cli_Model_Heuristics($enemyX, $enemyY);
            $h = $mHeuristics->calculateH($pathToMyEmptyCastle->x, $pathToMyEmptyCastle->y);
            if ($h < $enemy->getMovesLeft()) {
                $this->_fields->setCastleTemporaryType($pathToMyEmptyCastle->x, $pathToMyEmptyCastle->y, 'E');
                try {
                    $aStar = new Cli_Model_Astar($enemy, $pathToMyEmptyCastle->x, $pathToMyEmptyCastle->y, $this->_fields, $playerColor);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }
                $this->_fields->resetCastleTemporaryType($pathToMyEmptyCastle->x, $pathToMyEmptyCastle->y);
                if ($enemy->unitsHaveRange($aStar->getPath($pathToMyEmptyCastle->x . '_' . $pathToMyEmptyCastle->y, $playerColor))) {
                    return true;
                }
            }
        }
    }

    public function getPathToEnemyCastleInRange($playerId, $castleId, $army)
    {
        $this->_l->logMethodName();
        $playerColor = $this->getPlayerColor($playerId);
        $castle = $this->_players[$playerColor]->getCastle($castleId);
        $castleX = $castle->getX();
        $castleY = $castle->getY();

        $this->_fields->setCastleTemporaryType($castleX, $castleY, 'E');

        try {
            $aStar = new Cli_Model_Astar($army, $castleX, $castleY, $this->_fields, $playerColor);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $army->calculateMovesSpend($aStar->getPath($castleX . '_' . $castleY, $playerColor));
        if ($this->_fields->isEnemyCastle($playerColor, $move->x, $move->y)) {
            $move->in = true;
        } else {
            $move->in = false;
        }

        return $move;
    }

    public function getPathToEnemyInRange($playerId, $army, $enemy)
    {
        $this->_l->logMethodName();
        $playerColor = $this->getPlayerColor($playerId);
        $enemyX = $enemy->getX();
        $enemyY = $enemy->getY();
        $this->_fields->setCastleTemporaryType($enemyX, $enemyY, 'E');
        try {
            $aStar = new Cli_Model_Astar($army, $enemyX, $enemyY, $this->_fields, $playerColor);
        } catch (Exception $e) {
            echo($e);
            return;
        }
        $this->_fields->resetCastleTemporaryType($enemyX, $enemyY);
        return $army->calculateMovesSpend($aStar->getPath($enemyX . '_' . $enemyY, $playerColor));
    }

    public function isEnemyArmyInRange($playerId, $army, $enemy)
    {
        $this->_l->logMethodName();
        $playerColor = $this->getPlayerColor($playerId);
        $enemyX = $enemy->getX();
        $enemyY = $enemy->getY();

        if ($castleId = $this->_fields->getCastleId($playerColor, $enemyX, $enemyY)) {
            $this->_fields->setCastleTemporaryType($enemyX, $enemyY, 'E');
        } else {
            $this->_fields->setTemporaryType($enemyX, $enemyY, 'E');
        }

        try {
            $aStar = new Cli_Model_Astar($army, $enemyX, $enemyY, $this->_fields, $playerColor);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $army->calculateMovesSpend($aStar->getPath($enemyX . '_' . $enemyY, $playerColor));
        if ($castleId) {
            $this->_fields->resetCastleTemporaryType($enemyX, $enemyY);
            if ($castleId == $this->_fields->getCastleId($move->x, $move->y)) {
                $move->castleId = $castleId;
                return $move;
            } else {
                $move->current = null;
                return $move;
            }
        } else {
            $this->_fields->resetTemporaryType($enemyX, $enemyY);
            if ($move->x == $enemyX && $move->y == $enemyY) {
                return $move;
            } else {
                $move->current = null;
                return $move;
            }
        }
    }

    public function getMyEmptyCastleInMyRange($playerId, $army)
    {
        $this->_l->logMethodName();
        $playerColor = $this->getPlayerColor($playerId);
        $movesLeft = $army->getMovesLeft();
        $armyX = $army->getX();
        $armyY = $army->getY();

        foreach ($this->_players[$playerColor]->getCastles() as $castleId => $castle) {
            $castleX = $castle->getX();
            $castleY = $castle->getY();
            if ($this->_players[$playerColor]->countCastleGarrison($castleId)) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($armyX, $armyY);
            $h = $mHeuristics->calculateH($castleX, $castleY);
            if ($h < $movesLeft) {
                try {
                    $aStar = new Cli_Model_Astar($army, $castleX, $castleY, $this->_fields, $playerColor);
                } catch (Exception $e) {
                    $this->_l->log($e);
                    return;
                }

                $move = $army->calculateMovesSpend($aStar->getPath($castleX . '_' . $castleY, $playerColor));
                if ($move->x == $castleX && $move->y == $castleY) {
                    return $move;
                }
            }
        }
    }
}