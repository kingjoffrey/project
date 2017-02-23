<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class SingleController
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

        if (isset($dataIn['test'])) {
            $mapList = $mMap->getTestMap($mapId);
        } else {
            $mapList = $mMap->getAllMultiMapsList();
        }

        $view->form = new Application_Form_Creategame(array(
            'mapId' => $mapId,
            'mapsList' => $mapList
        ));
        $dataIn['numberOfPlayers'] = $numberOfPlayers;

        if (isset($dataIn['mapId']) && $view->form->isValid($dataIn)) {
            $mGame = new Application_Model_Game (0, $db);
            $gameId = $mGame->createGame($dataIn, $user->parameters['playerId']);

            new Cli_Model_SingleStart($user, $handler, $gameId);
        } else {
            $view->addScriptPath(APPLICATION_PATH . '/views/scripts');
            $view->form->setView($view);

            $token = array(
                'type' => 'single',
                'action' => 'index',
                'data' => $view->render('single/index.phtml')
            );
            $handler->sendToUser($user, $token);
        }
    }
}
