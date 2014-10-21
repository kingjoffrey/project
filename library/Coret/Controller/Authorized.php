<?php

abstract class Coret_Controller_Authorized extends Coret_Controller_Frontend
{
    protected $_redirectNotAuthorized = 'logowanie';

    public function init()
    {
        parent::init();
        if (!$this->_auth->hasIdentity()) {
            $this->redirect('/' . Zend_Registry::get('lang') . '/' . $this->_redirectNotAuthorized);
        } else {
            $this->view->Logout();
        }
    }
}
