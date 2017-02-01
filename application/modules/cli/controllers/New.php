<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class NewController
{
    function join(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'new',
            'action' => 'join',
            'data' => $view->render('new/join.phtml')
        );
        $handler->sendToUser($user, $token);
    }

    function setup(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!isset($dataIn['gameId']) || empty($dataIn['gameId'])) {
            echo('New/setup: brak gameId' . "\n");
            return;
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
}
