<?php

class OverController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $db = $handler->getDb();

        $gameId = $dataIn['id'];
        if (empty($gameId)) {
            echo('Brak game ID!');
            return;
        }

        $mGameScore = new Application_Model_GameScore($db);
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $view->score = $mGameScore->get($gameId);
        $view->players = $mPlayersInGame->getGamePlayers();

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'over',
            'action' => 'index',
            'data' => $view->render('over/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}