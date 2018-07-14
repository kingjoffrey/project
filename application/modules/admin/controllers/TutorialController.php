<?php

class Admin_TutorialController extends Coret_Controller_Backend
{

    public function init()
    {
        $this->view->title = 'Tutorial';
        parent::init();
    }
}

