<?php

class SetupController extends Game_Controller_Gui
{
    public function indexAction()
    {
        $gameId = $this->_request->getParam('gameId');
        if (empty($gameId)) {
            throw new Exception('Brak gameId!');
        }

        $version = Zend_Registry::get('config')->version;

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/playerslist.css?v=' . $version);
        $this->view->headScript()->appendFile('/js/setup.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/new.js?v=' . $version);

        $mGame = new Application_Model_Game($gameId);
        $game = $mGame->getGame();
        $mMapPlayers = new Application_Model_MapPlayers($game['mapId']);

        $this->view->mapPlayers = $mMapPlayers->getAll();
        $this->view->game = $game;
        $this->view->gameId = $gameId;

        $mMap = new Application_Model_Map($game['mapId']);
        $map = $mMap->getMap();

        $this->view->map = $map['name'];

        $this->view->timeLimits = Application_Model_Limit::timeLimits();
        $this->view->turnTimeLimit = Application_Model_Limit::turnTimeLimit();

        $this->view->form = new Application_Form_Team(array('mapId' => $game['mapId']));
    }
}

