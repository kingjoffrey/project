<?php

class HalloffameController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $db = $handler->getDb();

        if (!isset($dataIn['page'])) {
            $dataIn['page'] = 1;
        }

        if (!isset($dataIn['m'])) {
            $dataIn['m'] = 3;
        }

//        $mPlayer = new Application_Model_Player($db);
//        $view->hallOfFame = $mPlayer->hallOfFame($dataIn['page']);

        $m = new Application_Model_GameScore($db);
        $view->hallOfFame = $m->getHallOfFame($dataIn['m']);

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'halloffame',
            'action' => 'index',
            'data' => $view->render('halloffame/index.phtml'),
            'menu' => Zend_Registry::get('config')->gameType
        );
        $handler->sendToUser($user, $token);
    }
}