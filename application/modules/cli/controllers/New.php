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
            'mapsList' => $mMap->getAllMultiMapsList()
        ));
        $dataIn['numberOfPlayers'] = $numberOfPlayers;

        if (isset($dataIn['mapId']) && $view->form->isValid($dataIn)) {
            $mGame = new Application_Model_Game (0, $db);
            $gameId = $mGame->createGame($dataIn, $user->parameters['playerId']);

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

        $mGame = new Application_Model_Game($dataIn['gameId'], $db);
        $game = $mGame->getGame();

        $mMapPlayers = new Application_Model_MapPlayers($game['mapId'], $db);
        $mMap = new Application_Model_Map($game['mapId'], $db);

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'new',
            'action' => 'setup',
            'data' => $view->render('new/setup.phtml'),
            'mapPlayers' => $mMapPlayers->getAll(),
            'gameId' => $dataIn['gameId'],
            'mapName' => $mMap->getName(),
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
