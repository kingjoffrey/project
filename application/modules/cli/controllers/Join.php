<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class JoinController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'join',
            'action' => 'index',
            'data' => $view->render('join/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}
