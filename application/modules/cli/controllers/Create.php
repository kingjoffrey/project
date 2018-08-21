<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;


/**
 * Create multiplayer game
 */
class CreateController
{
    private $_turnTimeLimit = 43200;
    private $_turnsLimit = 100;

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

        $view->form = new Application_Form_Creategame(array(
            'mapId' => $mapId,
            'mapsList' => $mMap->getAllMultiMapsList()
        ));

        if (isset($dataIn['mapId']) && $view->form->isValid($dataIn)) {
            $mMap = new Application_Model_Map($mapId, $db);
            $dataIn['numberOfPlayers'] = $mMap->getMaxPlayers();
            $dataIn['type'] = Zend_Registry::get('config')->gameType->multiplayer;
            $dataIn['turnTimeLimit'] = $this->_turnTimeLimit;
            $dataIn['turnsLimit'] = $this->_turnsLimit;

            $mGame = new Application_Model_Game (0, $db);
            $gameId = $mGame->createGame($dataIn, $user->parameters['playerId']);

            SetupController::index($user, $handler, array('gameId' => $gameId));
        } else {
            $view->addScriptPath(APPLICATION_PATH . '/views/scripts');
            $view->form->setView($view);

            $token = array(
                'type' => 'create',
                'action' => 'index',
                'data' => $view->render('create/index.phtml'),
                'info' => array(
                    'turnTimeLimit' => $this->_turnTimeLimit,
                    'turnsLimit' => $this->_turnsLimit
                )
            );
            $handler->sendToUser($user, $token);
        }
    }

    function map(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!isset($dataIn['mapId']) || empty($dataIn['mapId'])) {
            echo('Create/map: brak mapId' . "\n");
            return;
        }
        $db = $handler->getDb();

        $mMap = new Application_Model_Map($dataIn['mapId'], $db);
        $mMapFields = new Application_Model_MapFields($dataIn['mapId'], $db);
        $token = array(
            'type' => 'create',
            'action' => 'map',
            'number' => $mMap->getMaxPlayers(),
            'fields' => $mMapFields->getMapFields()
        );
        $handler->sendToUser($user, $token);
    }
}
