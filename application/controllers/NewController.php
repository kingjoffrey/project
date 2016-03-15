<?php

class NewController extends Game_Controller_Gui
{
    public function indexAction()
    {
        $this->view->form = new Application_Form_Creategame(array('mapId' => $this->_request->getParam('mapId')));
        if (!$this->_request->isPost()) {
            $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/playerslist.css?v=' . $this->_version);
            $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/new.css?v=' . $this->_version);

            $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/new/init.js?v=' . $this->_version);
            $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/new/new.js?v=' . $this->_version);
            $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/new/websocket.js?v=' . $this->_version);
            $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/new/websocketMessage.js?v=' . $this->_version);
            $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/new/websocketSend.js?v=' . $this->_version);
            return;
        }

        if ($this->view->form->isValid($this->_request->getPost())) {
            $modelGame = new Application_Model_Game ();
            $gameId = $modelGame->createGame($this->_request->getParams(), $this->_playerId);
            $this->redirect('/' . $this->_request->getParam('lang') . '/setup/index/gameId/' . $gameId);
        }
    }
}
