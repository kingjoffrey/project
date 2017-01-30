<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class ContactController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'contact',
            'action' => 'index',
            'data' => $view->render('contact/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}