<?php

class TestController extends Coret_Controller_Authorized
{
    protected $_redirectNotAuthorized = 'login';

    public function indexAction()
    {
        $this->_helper->layout->setLayout('test');
        $this->view->jquery();
        $this->view->headLink()->appendStylesheet('/css/test.css?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/three.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/test.js?v=' . Zend_Registry::get('config')->version);
    }
}