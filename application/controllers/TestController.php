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
//
//        $mPlayer = new Application_Model_Player();
//        foreach ($mPlayer->getPlayers() as $row) {
//            $mHero = new Application_Model_Hero($row['playerId']);
//            foreach ($mHero->getHeroes() as $row2) {
//                if (!$row2['name']) {
//                    $mHero->changeHeroName($row2['heroId'], $mNG->generateHeroName());
//                }
//            }
//        }
    }
}
