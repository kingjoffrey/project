<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class IndexController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $view = new Zend_View();

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'index',
            'action' => 'index',
            'data' => $view->render('index/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}