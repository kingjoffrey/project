<?php

class LoadController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $db = $handler->getDb();

        if (!isset($dataIn['page'])) {
            $dataIn['page'] = 1;
        }

        $mGame = new Application_Model_Game(0, $db);
        $paginator = $mGame->getMyGames($user->parameters['playerId'], $dataIn['page'], new Application_Model_PlayersInGame(0, $db));

        $mPlayer = new Application_Model_Player($db);

        foreach ($paginator as &$val) {
            $mPlayersInGame = new Application_Model_PlayersInGame($val['gameId'], $db);
            $val['players'] = $mPlayersInGame->getGamePlayers();
            $val['playerTurn'] = $mPlayer->getPlayer($val['turnPlayerId']);
            $mMapPlayers = new Application_Model_MapPlayers($val['mapId'], $db);
            $val['teams'] = $mMapPlayers->getMapPlayerIdToBackgroundColorRelations();
        }

        $view->myGames = $paginator;
        $view->timeLimits = Application_Model_Limit::timeLimits();
        $view->turnTimeLimit = Application_Model_Limit::turnTimeLimit();

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'load',
            'action' => 'index',
            'data' => $view->render('load/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}