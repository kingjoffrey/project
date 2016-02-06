<?php

class HelpController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/help.css?v=' . Zend_Registry::get('config')->version);

        $mUnit = new Application_Model_Unit();
        $this->view->list = $mUnit->getUnits();
    }

}

