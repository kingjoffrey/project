<?php

class Admin_MapcastlesController extends Coret_Controller_Backend {

    public function init() {
        $this->view->title = 'Zamki na mapie';
        parent::init();
    }

}