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


        $token = array(
            'type' => 'heroes',
            'action' => 'index',
            'data' => $view->render('heroes/index.phtml'),
            'list' => $mHero->getHeroesNameExp()
        );
        $handler->sendToUser($user, $token);
    }

    public function show(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!$heroId = $dataIn['id']) {
            return;
        }

        $db = $handler->getDb();
        $mHero = new Application_Model_Hero($user->parameters['playerId'], $db);

        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        if (isset($dataIn['name'])) {

        }

        $hero = $mHero->getHero($heroId);

        $view->name = $hero['name'];
        $view->moves = $hero['numberOfMoves'];
        $view->attack = $hero['attackPoints'];
        $view->defense = $hero['defensePoints'];
        $view->experience = $hero['experience'];

        $token = array(
            'type' => 'heroes',
            'action' => 'show',
            'data' => $view->render('heroes/show.phtml')
        );
        $handler->sendToUser($user, $token);
    }

    public function chest()
    {
//        $mChest = new Application_Model_Chest(Zend_Auth::getInstance()->getIdentity()->playerId);
//        $this->view->chest = $mChest->getAll();
//
//        $mArtifact = new Application_Model_Artifact();
//        $this->view->artifacts = $mArtifact->getArtifacts();
    }
}

