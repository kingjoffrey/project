<?php

class NewController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $db = $handler->getDb();

        if (!isset($dataIn['mapId'])) {
            $mMap = new Application_Model_Map(0, $db);
            $mapId = $mMap->getMinMapId();
        } else {
            $mapId = $dataIn['mapId'];
        }

        $mMapPlayers = new Application_Model_MapPlayers($mapId, $db);

        $view->form = new Application_Form_Creategame(array(
            'mapId' => $mapId,
            'numberOfPlayers' => $mMapPlayers->getNumberOfPlayersForNewGame(),
            'mapsList' => $mMap->getAllMultiMapsList()
        ));
        $view->form->setView($view);

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        if (isset($dataIn['mapId']) && $view->form->isValid($dataIn)) {
            $mGame = new Application_Model_Game (0, $db);
            $gameId = $mGame->createGame($view->form->getValues(), $user->parameters['playerId']);

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

            $token = array(
                'type' => 'new',
                'action' => 'setup',
                'data' => $view->render('new/setup.phtml')
            );
        } else {
            $token = array(
                'type' => 'new',
                'action' => 'index',
                'data' => $view->render('new/index.phtml')
            );
        }
        $handler->sendToUser($user, $token);
    }
}