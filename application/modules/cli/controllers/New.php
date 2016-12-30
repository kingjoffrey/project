<?php

class NewController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $db = $handler->getDb();

        $mMap = new Application_Model_Map(0, $db);
        if (!isset($dataIn['mapId'])) {
            $mapId = $mMap->getMinMapId();
        } else {
            $mapId = $dataIn['mapId'];
        }

        $mMapPlayers = new Application_Model_MapPlayers($mapId, $db);
        $numberOfPlayers = $mMapPlayers->getNumberOfPlayersForNewGame();

        $view->form = new Application_Form_Creategame(array(
            'mapId' => $mapId,
            'numberOfPlayers' => $numberOfPlayers,
            'mapsList' => $mMap->getAllMultiMapsList()
        ));
        $dataIn['numberOfPlayers'] = $numberOfPlayers;

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        if (isset($dataIn['mapId']) && $view->form->isValid($dataIn)) {
            $mGame = new Application_Model_Game (0, $db);
            $gameId = $mGame->createGame($view->form->getValues(), $user->parameters['playerId']);

            $mGame = new Application_Model_Game($gameId, $db);
            $game = $mGame->getGame();

            $view->mapPlayers = $mMapPlayers->getAll();
            $view->game = $game;

            $mMap = new Application_Model_Map($game['mapId'], $db);
            $map = $mMap->getMap();

            $view->map = $map['name'];

            $view->timeLimits = Application_Model_Limit::timeLimits();
            $view->turnTimeLimit = Application_Model_Limit::turnTimeLimit();

            $view->form = new Application_Form_Team(array('longNames' => $mMapPlayers->getLongNames()));
            $view->form->setView($view);

            $token = array(
                'type' => 'new',
                'action' => 'setup',
                'data' => $view->render('new/setup.phtml'),
                'mapPlayers' => $mMapPlayers->getAll(),
                'numberOfPlayers' => $game['numberOfPlayers'],
                'form' => $view->form->__toString(),
                'gameId' => $gameId
            );
        } else {
            $view->form->setView($view);

            $token = array(
                'type' => 'new',
                'action' => 'index',
                'data' => $view->render('new/index.phtml')
            );
        }
        $handler->sendToUser($user, $token);
    }

    function map(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $db = $handler->getDb();

        $mMapPlayers = new Application_Model_MapPlayers($dataIn['mapId'], $db);
        $mMapFields = new Application_Model_MapFields($dataIn['mapId'], $db);
        $token = array(
            'type' => 'new',
            'action' => 'map',
            'number' => $mMapPlayers->getNumberOfPlayersForNewGame(),
            'fields' => $mMapFields->getMapFields()
        );
        $handler->sendToUser($user, $token);
    }
}