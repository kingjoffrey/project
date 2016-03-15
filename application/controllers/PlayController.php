<?php

class PlayController extends Game_Controller_Gui
{
    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/new.css?v=' . $this->_version);
        $mTutorial = new Application_Model_Tutorial($this->_playerId);
        $this->view->tutorial = $mTutorial->get();
    }
}

