<?php

class Admin_ArmyController extends Coret_Controller_Backend
{

    public function init()
    {
        $this->view->title = 'Armia';
        $this->view->controllerName = 'Army';
        parent::init();
    }

}

