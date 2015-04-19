<?php

class HalloffameController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $this->view->headScript()->appendFile('/js/halloffame.js?v=' . Zend_Registry::get('config')->version);
        $mPlayer = new Application_Model_Player();
        $this->view->hallOfFame = $mPlayer->hallOfFame($this->_request->getParam('page'));
    }

}

