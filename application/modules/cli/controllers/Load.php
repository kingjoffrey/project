<?php

class LoadController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $db = $handler->getDb();

        $mGame = new Application_Model_Game(0, $db);
        $view->myGames = $mGame->getMyGames($user->parameters['playerId'], $dataIn['page']);
        $view->timeLimits = Application_Model_Limit::timeLimits();
        $view->turnTimeLimit = Application_Model_Limit::turnTimeLimit();

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'load',
            'action' => 'index',
            'data' => $view->render('load/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}