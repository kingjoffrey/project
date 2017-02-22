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

        foreach ($myGames as &$game) {
            $mPlayersInGame = new Application_Model_PlayersInGame($game['gameId'], $db);
            $game['players'] = $mPlayersInGame->getGamePlayers();
            $game['playerTurn'] = $mPlayer->getPlayer($game['turnPlayerId']);
            $mMapPlayers = new Application_Model_MapPlayers($game['mapId'], $db);
            $game['teams'] = $mMapPlayers->getMapPlayerIdToBackgroundColorRelations();
            $game['begin'] = Coret_View_Helper_Formatuj::date($game['begin'], 'Y.m.d H:i');
            $game['end'] = Coret_View_Helper_Formatuj::date($game['end'], 'Y.m.d H:i');
        }

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'load',
            'action' => 'index',
            'data' => $view->render('load/index.phtml'),
            'games' => $myGames
        );
        $handler->sendToUser($user, $token);
    }
}
