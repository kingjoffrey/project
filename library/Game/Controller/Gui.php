<?php

abstract class Game_Controller_Gui extends Coret_Controller_Authorized
{
    protected $_redirectNotAuthorized = 'login';
    protected $_playerId;

    public function init()
    {
        parent::init();

        $this->_playerId = $this->_auth->getIdentity()->playerId;

        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css?v=' . Zend_Registry::get('config')->version);

        $this->view->jquery();
        $this->view->headScript()->appendFile('/js/default.js?v=' . Zend_Registry::get('config')->version);

        $this->view->MainMenu();
        $this->view->googleAnalytics();
        $this->view->Version();
        $this->view->Websocket($this->_playerId, 'xxx');
    }

}
