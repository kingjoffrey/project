<?php

class NewController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $this->view->form = new Application_Form_Creategame(array('mapId' => $this->_request->getParam('mapId')));
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $modelGame = new Application_Model_Game ();
                $gameId = $modelGame->createGame($this->_request->getParams(), Zend_Auth::getInstance()->getIdentity()->playerId);
                if ($gameId) {
                    $mMapPlayers = new Application_Model_MapPlayers($this->_request->getParam('mapId'));
                    $mPlayersInGame = new Application_Model_PlayersInGame($gameId);
                    $mPlayersInGame->joinGame(Zend_Auth::getInstance()->getIdentity()->playerId);
                    $mPlayersInGame->updatePlayerReady(Zend_Auth::getInstance()->getIdentity()->playerId, $mMapPlayers->getFirstMapPlayerId());
                    $this->redirect('/' . Zend_Registry::get('lang') . '/setup/index/gameId/' . $gameId);
                }
            }
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/playerslist.css?v=' . Zend_Registry::get('config')->version);
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/new.css?v=' . Zend_Registry::get('config')->version);

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/new.js?v=' . Zend_Registry::get('config')->version);

    }

}

