<?php

class Admin_UnitController extends Coret_Controller_Backend
{

    public function init()
    {
        $this->view->title = 'Unit';
        $this->view->controllerName = 'Unit';
        parent::init();
    }

}

