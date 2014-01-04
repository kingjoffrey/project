<?php

abstract class Game_Controller_Game extends Game_Controller_Action
{

    public function init()
    {
        parent::init();

        if (empty($this->_namespace->player['playerId'])) {
            $this->_redirect('/login');
        }

        $this->_namespace->gameId = $this->_request->getParam('id');
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }

        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css?v=' . Zend_Registry::get('config')->version);
        $this->view->jquery();
        $this->view->headScript()->appendFile('/js/kinetic-v4.7.4.min.js');
        $this->view->headScript()->appendFile('/js/date.js');

        $this->view->Websocket();
        $this->view->googleAnalytics();

    }

}
