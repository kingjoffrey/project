<?php

class Admin_TournamentplayersController extends Coret_Controller_Backend
{

    public function init()
    {
        $this->view->title = 'Gracze turniejowi';
        parent::init();
    }
}

