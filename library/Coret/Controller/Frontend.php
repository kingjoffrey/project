<?php

abstract class Coret_Controller_Frontend extends Zend_Controller_Action
{
    protected $_auth;

    public function init()
    {
        parent::init();

        $this->view->menu();

        $auth = Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session($this->getRequest()->getParam('module')));
        if ($auth->hasIdentity()) {
//            if ($this->getRequest()->getParam('controller') != 'logowanie') {
//                if ($this->getRequest()->getParam('controller') != 'profil' || $this->getRequest()->getParam('action') != 'haslo') {
//                    $this->userHaveToChangePassword();
//                }
//            }
            $this->view->usermenu();
        } else {
            $this->view->guestmenu();
        }

        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/colorbox.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/styles_text.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/styles_layout.css');

        $this->_helper->layout->setLayout('default');

        $this->view->headMeta()->appendHttpEquiv('Content-Language', Zend_Registry::get('lang'));
        $this->view->jquery();
        $this->view->headScript()->appendFile($this->view->baseUrl('/js/jquery.colorbox.js'));
        $this->view->headScript()->appendFile('/js/default.js');
        $this->view->headScript()->appendScript('var lang = "' . Zend_Registry::get('lang') . '"');
        $this->view->copyright();
        $this->view->guildName();
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
