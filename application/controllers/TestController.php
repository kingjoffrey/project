<?php

class TestController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->layout->setLayout('login');
//        $this->view->jquery();
//        $this->view->headScript()->appendFile('/js/three/three.js');
//        $this->view->headScript()->appendFile('/js/test.js');

//        $mNG = new Cli_Model_NameGenerator();
//        echo $mNG->generateHeroName() . "\n";
//        echo $mNG->generateHeroName() . "\n";
//        echo $mNG->generateHeroName() . "\n";
//        echo $mNG->generateHeroName() . "\n";
//        echo $mNG->generateHeroName() . "\n";
//        echo $mNG->generateHeroName() . "\n";
//        echo $mNG->generateHeroName() . "\n";
//        echo $mNG->generateHeroName() . "\n";
//        echo $mNG->generateHeroName() . "\n";
//        echo $mNG->generateHeroName() . "\n";
//        echo $mNG->generateHeroName() . "\n";

        $mHero = new Application_Model_Hero(73);
        $hero = $mHero->getFirstHero();
        print_r($hero);
    }
}

