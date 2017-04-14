<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class TournamentController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'tournament',
            'action' => 'index',
            'data' => $view->render('tournament/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}