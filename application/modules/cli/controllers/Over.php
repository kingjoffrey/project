<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class OverController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $db = $handler->getDb();

        $gameId = $dataIn['id'];
        if (empty($gameId)) {
            echo('Brak game ID!');
            return;
        }

        $mGameScore = new Application_Model_GameScore($db);
        $view->score = $mGameScore->getYourScore($gameId, $user->parameters['playerId']);

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'over',
            'action' => 'index',
            'data' => $view->render('over/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}