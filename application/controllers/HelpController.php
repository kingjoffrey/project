<?php

class HelpController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/help.css?v=' . $this->_version);

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/help/init.js?v=' . $this->_version);
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/help/help.js?v=' . $this->_version);
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/help/websocket.js?v=' . $this->_version);
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/help/websocketMessage.js?v=' . $this->_version);
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/help/websocketSend.js?v=' . $this->_version);

        $this->view->helpMenu();

//        $mUnit = new Application_Model_Unit();
//        $this->view->list = $mUnit->getUnits();
    }

}

