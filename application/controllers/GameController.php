<?php

class GameController extends Game_Controller_Game
{

    public function indexAction()
    {
        $this->_helper->layout->setLayout('game');

        $mGame = new Application_Model_Game($this->_namespace->gameId);

        $this->view->headLink()->appendStylesheet('/css/game.css?v=' . Zend_Registry::get('config')->version);

        $this->view->headScript()->appendFile('/js/jquery-ui-1.10.3.custom.js');

        $this->view->headScript()->appendFile('/js/game/init.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/castles.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/armies.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/astar.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/gui.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/move.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/towers.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/ruins.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/test.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/chat.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/chest.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/libs.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/zoom.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/websocket.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/message.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/timer.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/players.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/turn.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/game/sound.js?v=' . Zend_Registry::get('config')->version);

        $this->view->sound();

        $mCastlesInGame = new Application_Model_CastlesInGame($this->_namespace->gameId);
        $mArmy = new Application_Model_Army($this->_namespace->gameId);
        $mRuin = new Application_Model_RuinsInGame($this->_namespace->gameId);
        $mTower = new Application_Model_TowersInGame($this->_namespace->gameId);
        $mChat = new Application_Model_Chat($this->_namespace->gameId);
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_namespace->gameId);

        $game = $mGame->getGame();
        $this->view->gameBegin = $game['begin'];

        $mMapPlayers = new Application_Model_MapPlayers($game['mapId']);
        $this->view->capitals = $mMapPlayers->getCapitals();

        $mUnit = new Application_Model_MapUnits($game['mapId']);
        $this->view->units = $mUnit->getUnits();
        $mTerrain = new Application_Model_MapTerrain($game['mapId']);
        $this->view->terrain = $mTerrain->getTerrain();
        $mMapTowers = new Application_Model_MapTowers($game['mapId']);
        $neutralTowers = $mMapTowers->getMapTowers();
        $playersTowers = $mTower->getTowers();

        $mTurn = new Application_Model_TurnHistory($this->_namespace->gameId);
        $this->view->turnHistory = $mTurn->getTurnHistory();

        if (empty($this->view->turnHistory)) {
            $mTurn->add($game['turnPlayerId'], $game['turnNumber']);
            $this->view->turnHistory = $mTurn->getTurnHistory();
        }

        $towers = array();

        foreach (array_keys($neutralTowers) as $k) {
            $towers[$k] = $neutralTowers[$k];
            if (isset($playersTowers[$k])) {
                $towers[$k]['color'] = $playersTowers[$k];
            } else {
                $towers[$k]['color'] = 'neutral';
            }
        }

        $this->view->towers = $towers;

        $players = $mPlayersInGame->getPlayersInGame();

        $this->view->players = array();
        $colors = array();

        $mMapFields = new Application_Model_MapFields($game['mapId']);
        $mMapCastles = new Application_Model_MapCastles($game['mapId']);
        $this->view->map($game['mapId']);

        foreach ($players as $player) {
            $colors[$player['playerId']] = $player['color'];
            $this->view->players[$player['color']]['armies'] = array();

            $mHeroesInGame = new Application_Model_HeroesInGame($this->_namespace->gameId);
            $mSoldier = new Application_Model_UnitsInGame($this->_namespace->gameId);

            foreach ($mArmy->getPlayerArmies($player['playerId']) as $army) {
                $this->view->players[$player['color']]['armies'][$army['armyId']] = $army;
                $this->view->players[$player['color']]['armies'][$army['armyId']]['heroes'] = $mHeroesInGame->getForMove($army['armyId']);


                foreach ($this->view->players[$player['color']]['armies'][$army['armyId']]['heroes'] as $k => $row) {
                    $mInventory = new Application_Model_Inventory($row['heroId'], $this->_namespace->gameId);
                    $this->view->players[$player['color']]['armies'][$army['armyId']]['heroes'][$k]['artifacts'] = $mInventory->getAll();
                }

                $this->view->players[$player['color']]['armies'][$army['armyId']]['soldiers'] = $mSoldier->getForMove($army['armyId']);
                if (empty($this->view->players[$player['color']]['armies'][$army['armyId']]['heroes']) AND empty($this->view->players[$player['color']]['armies'][$army['armyId']]['soldiers'])) {
                    $mArmy->destroyArmy($army['armyId'], $player['playerId']);
                    unset($this->view->players[$player['color']]['armies'][$army['armyId']]);
                }
            }

            $this->view->players[$player['color']]['castles'] = $mCastlesInGame->getPlayerCastles($player['playerId']);
            $this->view->players[$player['color']]['turnActive'] = $player['turnActive'];
            $this->view->players[$player['color']]['computer'] = $player['computer'];
            $this->view->players[$player['color']]['lost'] = $player['lost'];
            $this->view->players[$player['color']]['minimapColor'] = $player['minimapColor'];
            $this->view->players[$player['color']]['backgroundColor'] = $player['backgroundColor'];
            $this->view->players[$player['color']]['textColor'] = $player['textColor'];
            $this->view->players[$player['color']]['longName'] = $player['longName'];
            $this->view->players[$player['color']]['team'] = $mMapPlayers->getColorByMapPlayerId($player['team']);

            if ($this->_namespace->player['playerId'] == $player['playerId']) {
                $this->view->gold = $player['gold'];
                $this->view->accessKey = $player['accessKey'];
                $this->view->color = $player['color'];
            }
        }

        $this->view->id = $this->_namespace->player['playerId'];
        if ($game['turnPlayerId'] == $this->_namespace->player['playerId']) {
            $this->view->myTurn = 'true';
        } else {
            $this->view->myTurn = 'false';
        }

        $gameMasterId = $mGame->getGameMasterId();
        if ($gameMasterId == $this->_namespace->player['playerId']) {
            $this->view->myGame = 1;
        } else {
            $this->view->myGame = 0;
        }

        $this->view->castlesSchema = array();
        $razed = $mCastlesInGame->getRazedCastles();
        $mMapRuins = new Application_Model_MapRuins($game['mapId']);
        $this->view->ruins = $mMapRuins->getMapRuins();
        $emptyRuins = $mRuin->getVisited();
        foreach (array_keys($emptyRuins) as $id) {
            $this->view->ruins[$id]['e'] = 1;
        }

        $mCastleProduction = new Application_Model_CastleProduction();
        $this->view->fields = $mMapFields->getMapFields();
        foreach ($mMapCastles->getMapCastles() as $id => $castle) {
            if (!isset($razed[$id])) {
                $castle['production'] = $mCastleProduction->getCastleProduction($id);
                $this->view->castlesSchema[$id] = $castle;
            }
        }

        $this->view->chatHistory = $mChat->getChatHistory();
        foreach ($this->view->chatHistory as $k => $v) {
            $this->view->chatHistory[$k]['color'] = $colors[$v['playerId']];
        }
        $this->view->gameId = $this->_namespace->gameId;

        $mBattleSequence = new Application_Model_BattleSequence($this->_namespace->gameId);
        $this->view->battleSequence = $mBattleSequence->get($this->_namespace->player['playerId']);
        if (empty($this->view->battleSequence)) {
            $mBattleSequence->initiate($this->_namespace->player['playerId'], $this->view->units);
            $this->view->battleSequence = $mBattleSequence->get($this->_namespace->player['playerId']);
        }
    }
}

