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

        $token = array(
            'type' => 'heroes',
            'action' => 'show',
            'data' => $view->render('heroes/show.phtml'),
            'name' => $hero['name'],
            'moves' => $hero['numberOfMoves'],
            'attack' => $hero['attackPoints'] + 1,
            'defense' => $hero['defensePoints'] + 1,
            'experience' => $hero['experience'],
            'bonus' => $mHeroSkills->getBonuses($heroId),
            'id' => $heroId
        );

        $token['level'] = $this->getCurrentLevel($hero['experience']);

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

        if ($hero = $mHero->getHero($heroId)) {
            $mHeroSkills = new Application_Model_Heroskills($db);

            $bonuses = $mHeroSkills->getBonuses($heroId);
            $level = $this->getCurrentLevel($hero['experience']);

            if (count($bonuses) >= $level) {
                return;
            }

            $bonuses[] = $levelBonusId;

            if ($mHeroSkills->up($heroId, $levelBonusId)) {
                $token = array(
                    'type' => 'heroes',
                    'action' => 'show',
                    'level' => $level,
                    'bonus' => $bonuses
                );
                $handler->sendToUser($user, $token);
            }
        }
    }

    private function getCurrentLevel($experience)
    {
        foreach ($this->_exp_2_level as $key => $val) {
            if ($experience < $val) {
                $level = $key - 1;
                break;
            }
        }

        return $level;
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

