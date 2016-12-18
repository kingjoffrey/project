<?php

class HelpController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $view = new Zend_View();
//        $view->helpMenu();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'help',
            'action' => 'index',
            'data' => $view->render('help/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}