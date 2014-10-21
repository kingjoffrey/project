<?php

class LoadController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/playerslist.css');
        $mGame = new Application_Model_Game();
        $this->view->myGames = $mGame->getMyGames(Zend_Auth::getInstance()->getIdentity()->playerId, $this->_request->getParam('page'));
        $this->view->timeLimits = Application_Model_Limit::timeLimits();
        $this->view->turnTimeLimit = Application_Model_Limit::turnTimeLimit();
    }

}

