<?php

class StartController extends Game_Controller_Gui
{

    public function _init()
    {
        $this->view->Websocket();
    }

    public function indexAction()
    {
        $this->_helper->layout->setLayout('start');

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/start.css?v=' . Zend_Registry::get('config')->version);

        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak gameId!');
        }

        $this->view->gameId = $this->_namespace->gameId;
    }

}

