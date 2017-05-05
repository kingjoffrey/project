<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class HeroesController
{
    private $_exp_2_level = array(0, 10, 20, 40, 80, 160, 320, 640, 1280, 2560, 5120);

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

        $hero = $mHero->getHero($heroId);

        $mHeroSkills = new Application_Model_Heroskills($db);
        $levelBonuses = $mHeroSkills->getBonuses($heroId);

        $token = array(
            'type' => 'heroes',
            'action' => 'show',
            'data' => $view->render('heroes/show.phtml'),
            'name' => $hero['name'],
            'moves' => $hero['numberOfMoves'],
            'attack' => $hero['attackPoints'] + 1,
            'defense' => $hero['defensePoints'] + 1,
            'experience' => $hero['experience'],
            'bonus' => $levelBonuses,
            'id' => $heroId
        );

        foreach ($this->_exp_2_level as $level => $exp) {
            if ($hero['experience'] < $exp) {
                $token['level'] = $level - 1;
                break;
            }
        }

        $handler->sendToUser($user, $token);
    }

    public function up(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!$levelBonusId = $dataIn['lbId']) {
            return;
        }

        if (!$heroId = $dataIn['hId']) {
            return;
        }

        $db = $handler->getDb();
        $mHero = new Application_Model_Hero($user->parameters['playerId'], $db);
        $mHeroSkills = new Application_Model_Heroskills($db);

        if ($hero = $mHero->getHero($heroId)) {

            $currentLevel = $mHeroSkills->getLevel($heroId);

            foreach ($this->_exp_2_level as $level => $exp) {
                if ($hero['experience'] < $exp) {
                    $nextLevel = $level - 1;
                    if ($nextLevel > $currentLevel) {
                        if ($mHeroSkills->up($heroId, $nextLevel, $levelBonusId)) {
                            $token = array(
                                'type' => 'heroes',
                                'action' => 'show',
                                'bonus' => $mHeroSkills->getBonuses($heroId)
                            );
                            $handler->sendToUser($user, $token);
                        }
                    }
                    break;
                }
            }
        }
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

