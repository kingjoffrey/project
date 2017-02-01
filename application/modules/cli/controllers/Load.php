<?php

class LoadController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $db = $handler->getDb();

        $mGame = new Application_Model_Game(0, $db);
        $myGames = $mGame->getMyGames($user->parameters['playerId'], new Application_Model_PlayersInGame(0, $db));

        $mPlayer = new Application_Model_Player($db);

        foreach ($myGames as &$val) {
            $mPlayersInGame = new Application_Model_PlayersInGame($val['gameId'], $db);
            $val['players'] = $mPlayersInGame->getGamePlayers();
            $val['playerTurn'] = $mPlayer->getPlayer($val['turnPlayerId']);
            $mMapPlayers = new Application_Model_MapPlayers($val['mapId'], $db);
            $val['teams'] = $mMapPlayers->getMapPlayerIdToBackgroundColorRelations();
        }

        $view->myGames = $myGames;

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'load',
            'action' => 'index',
            'data' => $view->render('load/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}