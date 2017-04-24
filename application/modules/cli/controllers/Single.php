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

        if (isset($dataIn['test'])) {
            $mapList = $mMap->getTestMap($mapId);
        } else {
            $mapList = $mMap->getAllMultiMapsList();
        }

        $view->form = new Application_Form_Creategame(array(
            'mapId' => $mapId,
            'mapsList' => $mapList
        ));

        if (isset($dataIn['mapId']) && $view->form->isValid($dataIn)) {
            $mMap = new Application_Model_Map($mapId, $db);
            $dataIn['numberOfPlayers'] = $mMap->getMaxPlayers();
            $dataIn['type'] = Zend_Registry::get('config')->game->type->singleplayer;

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
