<?php

class TestController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->layout->setLayout('login');
        $this->view->jquery();
        $this->view->headScript()->appendFile('/js/three/three.min.js');
        $this->view->headScript()->appendFile('/js/test.js?=2');

//        $mNG = new Cli_Model_CastleNameGenerator();
//        for ($i = 0; $i <= 10; $i++) {
//            echo $mNG->generateCastleName() . "\n";
//        }
    }
}
