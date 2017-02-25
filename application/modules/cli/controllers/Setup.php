<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class SetupController
{
    static function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!isset($dataIn['gameId']) || empty($dataIn['gameId'])) {
            echo('Setup: brak gameId' . "\n");
            return;
        }

        $view = new Zend_View();
        $db = $handler->getDb();

        $mGame = new Application_Model_Game($dataIn['gameId'], $db);

        $game = $mGame->getGame();

        $mMap = new Application_Model_Map($game['mapId'], $db);
        $mSide = new Application_Model_Side(0, $db);

        $maxPlayers = $mMap->getMaxPlayers();
        $teamMaxPlayers = $maxPlayers / 2;
        $sides = $mSide->getWithLimit($maxPlayers);
        $i = 0;
        $teams = array();

        foreach ($sides as $row) {
            $i++;
            if ($teamMaxPlayers >= $i) {
                $teamId = 1;
            } else {
                $teamId = 2;
            }

            $teams[$teamId]['sides'][] = $row;
        }

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'setup',
            'action' => 'index',
            'data' => $view->render('setup/index.phtml'),
            'teams' => $teams,
            'gameId' => $game['gameId'],
            'mapName' => $mMap->getName(),
            'gameMasterId' => $game['gameMasterId']
        );
        $handler->sendToUser($user, $token);
    }
}
