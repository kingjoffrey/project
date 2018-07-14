<?php

class Admin_HelpController extends Coret_Controller_Backend
{

    public function init()
    {
        $this->view->title = 'Pomoc';
        $this->view->controllerName = 'Help';
        parent::init();
    }

}

