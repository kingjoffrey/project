<?php

abstract class Coret_Controller_BackendAdmin extends Coret_Controller_Backend
{
    public function init()
    {
        parent::init();

        if (Zend_Auth::getInstance()->getIdentity()->type != 2) {
            $this->redirect('/admin');
        }
    }

}
