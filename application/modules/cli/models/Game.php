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

    private $_statistics;

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
            $this->_players[$this->getPlayerColor($playerId)]->getTeam(),
            $mPlayersInGame->getMe($playerId)
        );
        if ($this->_turnPlayerId == $playerId) {
            $this->_me->setTurn(true);
        }

        $this->initFields();
    }

    private function initFields()
    {
        foreach ($this->_players as $color => $player) {
            foreach ($player->getArmies() as $armyId => $army) {
                $this->_fields->addArmy($army->getX(), $army->getY(), $armyId, $color);
            }
            foreach ($player->getCastles() as $castleId => $castle) {
                $this->_fields->initCastle($castle->getX(), $castle->getY(), $castleId, $color);
            }
            foreach ($player->getTowers() as $towerId => $tower) {
                $this->_fields->initTower($tower->getX(), $tower->getY(), $towerId, $color);
            }
        }
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
//            'teams' => $this->_teams,
            'online' => $this->_online,
            'chatHistory' => $this->_chatHistory,
            'turnHistory' => $this->_turnHistory,
            'me' => $this->_me->toArray(),
            'players' => $this->_players->toArray(),
            'ruins' => $this->_ruins->toArray(),
            'neutralCastles' => $this->_players['neutral']->castlesToArray(),
            'neutralTowers' => $this->_players['neutral']->towersToArray()
        );
    }

    public function getPlayerCapital($color)
    {
        return $this->_capitals[$color];
    }

    public function getFields()
    {
        return $this->_fields;
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

    public function allEnemiesAreDead($playerId)
    {
        $playerColor = $this->getPlayerColor($playerId);
        $playerTeam = $this->_players->getPlayer($playerColor)->getTeam();
        foreach ($this->_players->get() as $color => $player) {
            if ($color == $playerColor || $playerTeam == $player->getTeam()) {
                continue;
            }
            if ($player->castlesExists() || $player->armiesExists()) {
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

        /* jeżeli nowa tura */
        if ($nextPlayerColor == $firstColor) {
            $this->turnNumberIncrement();
        }
        $this->_turnPlayerId = $this->getPlayers()->getPlayer($nextPlayerColor)->getId();

        $mGame = new Application_Model_Game($this->_id, $db);
        $mGame->updateTurn($this->_turnPlayerId, $this->_turnNumber);

        return $this->_turnPlayerId;
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

    public function activatePlayerTurn($playerId, $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_id, $db);
        $mPlayersInGame->turnActivate($playerId);

        $playerColor = $this->getPlayerColor($playerId);
        foreach ($this->_players as $color => $player) {
            if ($color == 'neutral') {
                continue;
            }
            if ($playerColor == $color) {
                $player->setTurnActive(true);
            } else {
                $player->setTurnActive(false);
            }
        }
    }

    public function setTurnPlayerId($playerId)
    {
        $this->_turnPlayerId = $playerId;
    }

    public function getTurnPlayerId()
    {
        return $this->_turnPlayerId;
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
            $this->_ruins->getRuin($ruinId)->setEmpty($this->_id, $db);
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
            $this->_ruins->getRuin($ruinId)->setEmpty($this->_id, $db);
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

    public function getEnemiesHaveRangeAtThisCastle($playerId, $castle)
    {
        $this->_l->logMethodName();
        $enemiesHaveRange = array();
        $playerColor = $this->getPlayerColor($playerId);
        $castleX = $castle->getX();
        $castleY = $castle->getY();

        foreach ($this->_players->get() as $color => $player) {
            if ($this->_players->sameTeam($playerColor, $color)) {
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

                    if ($enemy->unitsHaveRange($aStar->getPath($castleX . '_' . $castleY))) {
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

        foreach ($this->_players->get() as $color => $player) {
            if ($this->_players->sameTeam($playerColor, $color)) {
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

                    $move = $army->calculateMovesSpend($aStar->getPath($enemyX . '_' . $enemyY));
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

        foreach ($this->_ruins->get() as $ruinId => $ruin) {
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
                $path = $army->calculateMovesSpend($aStar->getPath($ruinX . '_' . $ruinY));
                if ($path->x == $ruinX && $path->y == $ruinY) {
                    $path->ruinId = $ruinId;
                    return $path;
                }
            }
        }
    }

    public function getWeakerHostileCastleId($playerId, $army, $castlesIds = array())
    {
        $this->_l->logMethodName();
        $playerColor = $this->getPlayerColor($playerId);
        $heuristics = array();
        $armyX = $army->getX();
        $armyY = $army->getY();

        foreach ($this->_players as $color => $player) {
            if ($this->_players->sameTeam($playerColor, $color)) {
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
            if ($this->_players['neutral']->hasCastle($id)) {
                $enemy = $this->_players['neutral']->getCastleGarrison($this->_turnNumber, $this->_firstUnitId);
            } else {
                $enemy = $this->getCastleGarrison($playerId, $id);
            }

            if (!$this->isEnemyStronger($playerId, $army, $enemy)) {
                return $id;
            }
        }
        return null;
    }

    public function findNearestWeakestHostileCastle($playerId, $army)
    {
        $this->_l->logMethodName();
        $omittedCastlesIds = array();
        $weakerHostileCastleId = $this->getWeakerHostileCastleId($playerId, $army);

        if (!$weakerHostileCastleId) {
            return new Cli_Model_Path();
        }

        $path = $this->getPathToEnemyCastleInRange($playerId, $army, $weakerHostileCastleId);
        while (true) {
            if (!isset($path->current) || empty($path->current)) {
                $omittedCastlesIds[] = $weakerHostileCastleId;
                $weakerHostileCastleId = $this->getWeakerHostileCastleId($playerId, $army, $omittedCastlesIds);
                if ($weakerHostileCastleId) {
                    $path = $this->getPathToEnemyCastleInRange($playerId, $army, $weakerHostileCastleId);
                } else {
                    break;
                }
            }
            break;
        }
        $path->castleId = $weakerHostileCastleId;
        return $path;
    }

    public function getPathToEnemyCastleInRange($playerId, $army, $castleId)
    {
        $this->_l->logMethodName();
        $playerColor = $this->getPlayerColor($playerId);

        foreach ($this->_players as $color => $player) {
            if ($player->hasCastle($castleId)) {
                $castle = $this->_players[$color]->getCastle($castleId);
                break;
            }
        }

        $castleX = $castle->getX();
        $castleY = $castle->getY();

        $this->_fields->setCastleTemporaryType($castleX, $castleY, 'E');

        try {
            $aStar = new Cli_Model_Astar($army, $castleX, $castleY, $this->_fields, $playerColor);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $army->calculateMovesSpend($aStar->getPath($castleX . '_' . $castleY));
        if ($move->end && $this->_fields->isEnemyCastle($playerColor, $move->x, $move->y)) {
            $move->in = true;
        } else {
            $move->in = false;
        }

        return $move;
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

        return $army->calculateMovesSpend($aStar->getPath($castleX . '_' . $castleY));
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
            foreach ($this->_players->get() as $color => $player) {
                if ($this->_players->sameTeam($playerColor, $color)) {
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

                $move = $army->calculateMovesSpend($aStar->getPath($armyX . '_' . $armyY));
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
            if ($this->_players->sameTeam($playerColor, $color)) {
                continue;
            }
            foreach ($player->getArmies() as $enemy) {
                $enemyX = $enemy->getX();
                $enemyY = $enemy->getY();

                $mHeuristics = new Cli_Model_Heuristics($enemyX, $enemyY);
                $h = $mHeuristics->calculateH($armyX, $armyY);
                if ($h < $movesLeft) {
                    if (!$this->isEnemyStronger($playerId, $army, $enemy)) {
                        continue;
                    }
                    $castleId = $this->_fields->isPlayerCastle($color, $enemyX, $enemyY);
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

                    $move = $army->calculateMovesSpend($aStar->getPath($enemyX . '_' . $enemyY));
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

        foreach ($this->_players as $color => $player) {
            if ($this->_players->sameTeam($playerColor, $color)) {
                continue;
            }
            foreach ($player->getArmies() as $enemy) {
                $enemyX = $enemy->getX();
                $enemyY = $enemy->getY();
                $mHeuristics = new Cli_Model_Heuristics($enemyX, $enemyY);
                $h = $mHeuristics->calculateH($armyX, $armyY);
                if ($h < $movesLeft) {
                    if ($this->isEnemyStronger($playerId, $army, $enemy)) {
                        continue;
                    }
                    $castleId = $this->_fields->isNotPlayerCastle($playerColor, $enemyX, $enemyY);
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

                    $move = $army->calculateMovesSpend($aStar->getPath($enemyX . '_' . $enemyY));
                    if ($castleId) {
                        $this->_fields->resetCastleTemporaryType($enemyX, $enemyY);
                    } else {
                        $this->_fields->resetTemporaryType($enemyX, $enemyY);
                    }
                    if ($move->x == $enemyX && $move->y == $enemyY) {
                        $move->castleId = $castleId;
                        $move->armyId = $enemy->getId();
                        return $move;
                    }
                }
            }
        }

        return;
    }

    public function canAttackAllEnemyHaveRange($playerId, $army, $enemiesHaveRange)
    {
        $this->_l->logMethodName();
        foreach ($enemiesHaveRange as $enemy) {
            if ($this->isEnemyStronger($playerId, $army, $enemy)) {
                return;
            }
        }
        return $enemy;
    }

    /**
     * @param $playerId
     * @param $pathToMyEmptyCastle
     * @return bool
     */
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
                if ($enemy->unitsHaveRange($aStar->getPath($pathToMyEmptyCastle->x . '_' . $pathToMyEmptyCastle->y))) {
                    return true;
                }
            }
        }
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
        return $army->calculateMovesSpend($aStar->getPath($enemyX . '_' . $enemyY));
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

        $move = $army->calculateMovesSpend($aStar->getPath($enemyX . '_' . $enemyY));
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

                $move = $army->calculateMovesSpend($aStar->getPath($castleX . '_' . $castleY));
                if ($move->x == $castleX && $move->y == $castleY) {
                    return $move;
                }
            }
        }
    }

    public function isEnemyStronger($playerId, Cli_Model_Army $army, Cli_Model_Army $enemy, $max = 30)
    {
        $this->_l->logMethodName();
        $playerColor = $this->getPlayerColor($playerId);
        $enemyX = $enemy->getX();
        $enemyY = $enemy->getY();
        $enemyColor = $this->_fields->getArmyColor($enemyX, $enemyY, $enemy->getId());
        $attackerWinsCount = 0;
        $attackerCourage = 2;

        if ($castleId = $this->_fields->isEnemyCastle($playerColor, $enemyX, $enemyY)) {
            $enemy->setDefenseModifier($this->_players->getPlayer($enemyColor)->getCastleDefenseModifier($castleId));
        } elseif ($this->_fields->getTowerId($enemyX, $enemyY)) {
            $enemy->setDefenseModifier(1);
        }

        $battle = new Cli_Model_Battle(
            $army,
            $enemy,
            $this->_players->getPlayer($playerColor)->getAttackSequence(),
            $this->_players->getPlayer($enemyColor)->getDefenceSequence()
        );

        for ($i = 0; $i < $max; $i++) {
            $battle->fight();
            if ($battle->attackerVictory()) {
                $attackerWinsCount++;
            }
        }

        $border = $max - $attackerWinsCount - $attackerCourage;
        if ($attackerWinsCount >= $border) {
            return false;
        } else {
            return true;
        }
    }
}