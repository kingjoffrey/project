<?php

class Admin_MapcastlesController extends Coret_Controller_Backend {

    public function init() {
        $this->view->title = 'Map castles';
        $this->view->controllerName = 'Mapcastles';
        parent::init();
    }

}