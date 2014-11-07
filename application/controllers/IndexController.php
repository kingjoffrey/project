<?php

class IndexController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $this->view->headScript()->appendFile('/js/index.js?v=' . Zend_Registry::get('config')->version);
        $this->view->title();
    }

    public function unsupportedAction()
    {

    }

}
