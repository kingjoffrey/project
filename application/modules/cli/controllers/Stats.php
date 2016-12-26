<?php

class StatsController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $view = new Zend_View();

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'stats',
            'action' => 'index',
            'data' => $view->render('stats/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}