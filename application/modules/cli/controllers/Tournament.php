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
            echo ('No tournamentId (show)') . "\n";
            return;
        }

        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $db = $handler->getDb();
        $mTournamentPlayers = new Application_Model_TournamentPlayers($db);

        $token = array(
            'type' => 'tournament',
        );

        $mTournamentGames = new Application_Model_TournamentGames($db);
        if ($gameId = $mTournamentGames->getGameId($tournamentId, $user->parameters['playerId'])) {
            $token['data'] = $view->render('tournament/play.phtml');
            $token['action'] = 'play';
            $token['id'] = $gameId;

            $handler->sendToUser($user, $token);

            return;
        }

        if ($mTournamentPlayers->playerPayment($tournamentId, $user->parameters['playerId'])) {
            $token['data'] = $view->render('tournament/list.phtml');
            $token['action'] = 'list';

            $token['list'] = $mTournamentPlayers->getPlayersNames($tournamentId);

            $handler->sendToUser($user, $token);

            return;
        }

        $mTournament = new Application_Model_Tournament($db);

        if ($mTournament->checkLimit($tournamentId) <= $mTournamentPlayers->countPlayers($tournamentId)) {
            $token['data'] = $view->render('tournament/full.phtml');
            $token['action'] = 'full';

            $handler->sendToUser($user, $token);

            return;
        }

        $view->fee = $mTournament->getFee($tournamentId);

        $token['data'] = $view->render('tournament/paypal.phtml');
        $token['action'] = 'paypal';
        $token['id'] = $tournamentId;

        $handler->sendToUser($user, $token);
    }
}