<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class CreateController
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
                'type' => 'create',
                'action' => 'index',
                'data' => $view->render('create/index.phtml')
            );
            $handler->sendToUser($user, $token);
        }
    }

    function map(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!isset($dataIn['mapId']) || empty($dataIn['mapId'])) {
            echo('New/map: brak mapId' . "\n");
            return;
        }
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
