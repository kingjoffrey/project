<?php

class Admin_MapController extends Coret_Controller_Backend
{

    public function init()
    {
        $this->view->title = 'Map';
        $this->view->controllerName = 'Map';
        parent::init();
    }
}

