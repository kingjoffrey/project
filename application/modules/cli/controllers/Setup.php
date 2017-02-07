<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class SetupController
{
    static function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!isset($dataIn['gameId']) || empty($dataIn['gameId'])) {
            echo('Setup: brak gameId' . "\n");
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
            'type' => 'setup',
            'action' => 'index',
            'data' => $view->render('setup/index.phtml'),
            'mapPlayers' => $mMapPlayers->getAll(),
            'gameId' => $game['gameId'],
            'mapName' => $mMap->getName(),
            'gameMasterId' => $game['gameMasterId']
        );
        $handler->sendToUser($user, $token);
    }
}
