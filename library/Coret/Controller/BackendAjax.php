<?php

abstract class Coret_Controller_BackendAjax extends Coret_Controller_Ajax
{
    public function init()
    {
        $auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session($this->getRequest()->getParam('module')));
        if (!$auth->hasIdentity()) {
            throw new Exception('Not authorized!');
        }
        parent::init();
    }

}
