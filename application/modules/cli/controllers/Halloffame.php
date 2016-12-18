<?php

class HalloffameController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $db = $handler->getDb();
        $mPlayer = new Application_Model_Player($db);
        $view->hallOfFame = $mPlayer->hallOfFame($dataIn['page']);

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'halloffame',
            'action' => 'index',
            'data' => $view->render('halloffame/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}