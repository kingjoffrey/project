<?php

class TestController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->_helper->layout->setLayout('test');
        $this->view->jquery();
        $this->view->headLink()->appendStylesheet('/css/test.css?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/three.js?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->appendFile('/js/test.js?v=' . Zend_Registry::get('config')->version);
    }
}