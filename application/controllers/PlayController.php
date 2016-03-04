<?php

class PlayController extends Game_Controller_Gui
{
    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/new.css?v=' . $this->_version);
    }
}

