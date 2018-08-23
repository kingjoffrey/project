<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class MessagesController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'messages',
            'action' => 'index',
            'data' => $view->render('messages/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }

    public function thread(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $view->id = $dataIn['id'];

        $token = array(
            'type' => 'messages',
            'action' => 'thread',
            'data' => $view->render('messages/thread.phtml')
        );

        $handler->sendToUser($user, $token);
    }
}