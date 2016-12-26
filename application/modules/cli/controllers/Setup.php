<?php

class SetupController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $gameId = $dataIn['gameId'];
        if (empty($gameId)) {
            echo('Setup: brak gameId!');
            return;
        }

        $view = new Zend_View();
        $db = $handler->getDb();

        $mGame = new Application_Model_Game($gameId, $db);
        $game = $mGame->getGame();
        $mMapPlayers = new Application_Model_MapPlayers($game['mapId'], $db);

        $view->mapPlayers = $mMapPlayers->getAll();
        $view->game = $game;

        $mMap = new Application_Model_Map($game['mapId'], $db);
        $map = $mMap->getMap();

        $view->map = $map['name'];

        $view->timeLimits = Application_Model_Limit::timeLimits();
        $view->turnTimeLimit = Application_Model_Limit::turnTimeLimit();

        $view->form = new Application_Form_Team(array('mapId' => $game['mapId']));

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'setup',
            'action' => 'index',
            'data' => $view->render('setup/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}