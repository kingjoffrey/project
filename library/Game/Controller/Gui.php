<?php

abstract class Game_Controller_Gui extends Coret_Controller_Authorized
{
    protected $_redirectNotAuthorized = 'login';
    protected $_playerId;
    protected $_version;

    public function init()
    {
        parent::init();

        $identity = $this->_auth->getIdentity();
        $this->_playerId = $identity->playerId;
        $this->_version = Zend_Registry::get('config')->version;

        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css?v=' . $this->_version);

        $this->view->jquery();
        $this->view->headScript()->appendFile('/js/default.js?v=' . $this->_version);

        $this->view->MainMenu();
        $this->view->Friends();
        $this->view->ChatInput();
        $this->view->ChatTitle();
        $this->view->FriendsTitle();
        $this->view->translations();
//        $this->view->googleAnalytics();
        $this->view->Version();

        $this->view->Websocket($identity);
    }

}
