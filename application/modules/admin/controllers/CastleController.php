<?php

class Admin_CastleController extends Coret_Controller_Backend
{

    public function init()
    {
        $this->view->title = 'Castle';
        parent::init();

//        $mCastle = new Admin_Model_Mapcastles(array());
//        $mCastle->castles(1);
    }

}

