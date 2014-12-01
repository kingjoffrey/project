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
            $this->_players->getPlayer($this->getPlayerColor($playerId))->getTeam(),
            $mPlayersInGame->getMe($playerId)
        );
        if ($this->_turnPlayerId == $playerId) {
            $this->_me->setTurn(true);
        }

        $this->initFields();
    }

    private function initFields()
    {
        foreach ($this->_players->get() as $color => $player) {
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
            'neutralCastles' => $this->_players->getPlayer('neutral')->getCastles()->toArray(),
            'neutralTowers' => $this->_players->getPlayer('neutral')->getTowers()->toArray()
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

    public function searchRuin($ruinId, Cli_Model_Army $army, $playerId, $db)
    {
        $random = rand(0, 100);
        $heroId = $army->getHeroes()->getAnyHeroId();

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


    public function handleCastleGarrison(Cli_Model_Castle $castle)
    {
        $enemies = array();
        $castleX = $castle->getX();
        $castleY = $castle->getY();

        for ($i = $castleX; $i <= $castleX + 1; $i++) {
            for ($j = $castleY; $j <= $castleY + 1; $j++) {
                foreach ($this->_fields->getArmies($i, $j) as $armyId => $color) {
                    $enemies[] = $this->_players->getPlayer($color)->getArmy($armyId);
                }
            }
        }

        return $enemies;
    }


}