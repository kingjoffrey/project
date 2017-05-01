<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class HeroesController
{

    public function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $db = $handler->getDb();
        $mHero = new Application_Model_Hero($user->parameters['playerId'], $db);

        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $view->heroes = $mHero->getHeroes();

//        $this->view->form = new Application_Form_Hero ();
//        $this->view->form->setDefault('name', $this->view->heroes[0]['name']);
//        if ($this->_request->isPost()) {
//            if ($this->view->form->isValid($this->_request->getPost())) {
//
//                $mHero->changeHeroName($this->view->heroes[0]['heroId'], $this->_request->getParam('name'));
//                $this->redirect('/hero');
//            }
//        }

//        $mChest = new Application_Model_Chest(Zend_Auth::getInstance()->getIdentity()->playerId);
//        $this->view->chest = $mChest->getAll();
//
//        $mArtifact = new Application_Model_Artifact();
//        $this->view->artifacts = $mArtifact->getArtifacts();


        $token = array(
            'type' => 'heroes',
            'action' => 'index',
            'data' => $view->render('heroes/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }

}

