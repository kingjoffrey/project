<?php

abstract class Coret_Controller_Authorized extends Coret_Controller_Frontend
{

    public function init()
    {
        parent::init();
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->redirect('/' . Zend_Registry::get('lang') . '/logowanie');
        }
    }
}
