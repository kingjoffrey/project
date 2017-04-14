<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class TournamentController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $db = $handler->getDb();
        $mTournament = new Application_Model_Tournament($db);

        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'tournament',
            'action' => 'index',
            'data' => $view->render('tournament/index.phtml'),
            'list' => $mTournament->getList()
        );
        $handler->sendToUser($user, $token);
    }

    function show(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!$tournamentId = $dataIn['id']) {
            return;
        }


        $db = $handler->getDb();

        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'tournament',
            'action' => 'show',
            'data' => $view->render('tournament/show.phtml'),
        );
        $handler->sendToUser($user, $token);
    }
}