<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class NewController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
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

        if (isset($dataIn['mapId']) && $view->form->isValid($dataIn)) {
            $mGame = new Application_Model_Game (0, $db);
            $gameId = $mGame->createGame($view->form->getValues(), $user->parameters['playerId']);

            $this->setup($user, $handler, array('gameId' => $gameId));
        } else {
            $view->addScriptPath(APPLICATION_PATH . '/views/scripts');
            $view->form->setView($view);

            $token = array(
                'type' => 'new',
                'action' => 'index',
                'data' => $view->render('new/index.phtml')
            );
            $handler->sendToUser($user, $token);
        }
    }

    function setup(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (empty($dataIn['gameId'])) {
            echo('New/setup: brak gameId' . "\n");
        }

        $view = new Zend_View();
        $db = $handler->getDb();

        $gameId = $dataIn['gameId'];

        $mGame = new Application_Model_Game($gameId, $db);
        $game = $mGame->getGame();

        $mMapPlayers = new Application_Model_MapPlayers($game['mapId'], $db);
        $view->mapPlayers = $mMapPlayers->getAll();
        $view->game = $game;

        $mMap = new Application_Model_Map($game['mapId'], $db);
        $view->mapName = $mMap->getName();

        $view->timeLimits = Application_Model_Limit::timeLimits();
        $view->turnTimeLimit = Application_Model_Limit::turnTimeLimit();

        $view->form = new Application_Form_Team(array('longNames' => $mMapPlayers->getShortNames()));
        $view->form->setView($view);

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'new',
            'action' => 'setup',
            'data' => $view->render('new/setup.phtml'),
            'mapPlayers' => $mMapPlayers->getAll(),
            'form' => $view->form->__toString(),
            'gameId' => $gameId,
            'gameMasterId' => $game['gameMasterId']
        );
        $handler->sendToUser($user, $token);
    }

    function map(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
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
