<?php

class PlayController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $view = new Zend_View();
        $db = $handler->getDb();

        $mTutorial = new Application_Model_TutorialProgress($user->parameters['playerId'], $db);
        $view->tutorial = $mTutorial->get();

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'play',
            'action' => 'index',
            'data' => $view->render('play/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}