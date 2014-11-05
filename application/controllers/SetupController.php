<?php

class SetupController extends Game_Controller_Gui
{
    public function indexAction()
    {
        $gameId = $this->_request->getParam('gameId');
        if (empty($gameId)) {
            throw new Exception('Brak gameId!');
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/playerslist.css?v=' . Zend_Registry::get('config')->version);
        $this->view->Websocket();
        $this->view->headScript()->appendFile('/js/setup.js?v=' . Zend_Registry::get('config')->version);

        $mGame = new Application_Model_Game($gameId);
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId);
        $game = $mGame->getGame();
        $mMapPlayers = new Application_Model_MapPlayers($game['mapId']);

        $mGame->updateGameMaster($this->_playerId);

        if ($game['gameMasterId'] != $this->_playerId) {
            if ($mPlayersInGame->isPlayerInGame($this->_playerId)) {
                $mPlayersInGame->disconnectFromGame($this->_playerId);
            }
            $mPlayersInGame->joinGame($this->_playerId);
        } elseif (!$mPlayersInGame->isPlayerInGame($this->_playerId)) {
            $mPlayersInGame->joinGame($this->_playerId);
        }

        $this->view->mapPlayers = $mMapPlayers->getAll();
        $this->view->game = $game;
        $this->view->accessKey = $mPlayersInGame->getAccessKey($this->_playerId);
        $this->view->gameId = $gameId;
        $this->view->playerId = $this->_playerId;

        $mMap = new Application_Model_Map($game['mapId']);
        $map = $mMap->getMap();

        $this->view->map = $map['name'];

        $this->view->timeLimits = Application_Model_Limit::timeLimits();
        $this->view->turnTimeLimit = Application_Model_Limit::turnTimeLimit();

        $this->view->form = new Application_Form_Team(array('mapId' => $game['mapId']));
    }
}

