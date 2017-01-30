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
                'type' => 'single',
                'action' => 'index',
                'data' => $view->render('single/index.phtml')
            );
            $handler->sendToUser($user, $token);
        }
    }
}
