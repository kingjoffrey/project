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

        if (isset($this->_namespace->armyId)) {
            unset($this->_namespace->armyId);
        }

        $this->_namespace->gameId = $gameId; // zapisuję gemeId do sesji

        $mGame = new Application_Model_Game($gameId);
        $mGame->updateGameMaster($this->_namespace->player['playerId']);

        $game = $mGame->getGame();

        $mPlayersInGame = new Application_Model_PlayersInGame($gameId);
        if ($game['gameMasterId'] != $this->_namespace->player['playerId']) {
            if ($mPlayersInGame->isPlayerInGame($this->_namespace->player['playerId'])) {
                $mPlayersInGame->disconnectFromGame($this->_namespace->player['playerId']);
            }
            $mPlayersInGame->joinGame($this->_namespace->player['playerId']);
        } elseif (!$mPlayersInGame->isPlayerInGame($this->_namespace->player['playerId'])) {
            $mPlayersInGame->joinGame($this->_namespace->player['playerId']);
        }

        $mMapPlayers = new Application_Model_MapPlayers($game['mapId']);

        $this->view->mapPlayers = $mMapPlayers->getAll();
        $this->view->game = $game;
        $this->view->accessKey = $mPlayersInGame->getAccessKey($this->_namespace->player['playerId']);
        $this->view->gameId = $gameId;
        $this->view->player = $this->_namespace->player;

        $mMap = new Application_Model_Map($game['mapId']);
        $map = $mMap->getMap();

        $this->view->map = $map['name'];

        $this->view->timeLimits = Application_Model_Limit::timeLimits();
        $this->view->turnTimeLimit = Application_Model_Limit::turnTimeLimit();

        $this->view->form = new Application_Form_Team(array('mapId' => $game['mapId']));
    }
}

