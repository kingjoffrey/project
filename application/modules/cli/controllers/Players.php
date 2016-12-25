<?php

class PlayersController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $view->searchForm = new Application_Form_Search();
        $view->searchForm->setView($view);

        if (isset($dataIn['search']) && $dataIn['search']) {
            if ($view->searchForm->isValid($dataIn)) {
                $db = $handler->getDb();
                $mPlayer = new Application_Model_Player($db);
                $view->searchResults = $mPlayer->search($view->searchForm->getValue('search'));
            }
        }

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'players',
            'action' => 'index',
            'data' => $view->render('players/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }

    function add(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if ($dataIn['friendId']) {
            $db = $handler->getDb();
            $mFriend = new Application_Model_Friends($db);
            $mFriend->create($user->parameters['playerId'], $dataIn['friendId']);
        }

        $view = new Zend_View();
        $view->searchForm = new Application_Form_Search();
        $view->searchForm->setView($view);

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'players',
            'action' => 'index',
            'data' => $view->render('players/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}