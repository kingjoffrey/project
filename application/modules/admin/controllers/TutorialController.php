<?php

class Admin_TutorialController extends Coret_Controller_Backend
{

    public function init()
    {
        $this->view->title = 'Tutorial';
        $this->view->controllerName = 'Tutorial';
        parent::init();
    }
}

