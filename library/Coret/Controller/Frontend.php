<?php

abstract class Coret_Controller_Frontend extends Zend_Controller_Action
{
    protected $_auth;

    public function init()
    {
        parent::init();

        $this->_auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session($this->getRequest()->getParam('module')));

        $this->_helper->layout->setLayout('default');

        $this->view->headMeta()->appendHttpEquiv('Content-Language', Zend_Registry::get('lang'));
        $this->view->headScript()->appendScript('var lang = "' . Zend_Registry::get('lang') . '"');
    }

    protected function userHaveToChangePassword()
    {
        $adminClassName = Zend_Registry::get('config')->adminClassName;
        if (!$adminClassName) {
            throw new Zend_Exception('Admin class name not enabled in application.ini');
        }
        $mAdmin = new $adminClassName();
        if ($mAdmin->userHaveToChangePassword()) {
            $this->redirect($this->view->url(array('controller' => 'profil', 'action' => 'haslo')));
            return true;
        }
    }

}
