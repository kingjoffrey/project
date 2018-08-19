<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class LoadController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();

        $menu = array();

        foreach (Zend_Registry::get('config')->gameType as $key => $val) {
            if ($val == 0) {
                continue;
            }
            $menu[$val] = $key;
        }

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'load',
            'action' => 'index',
            'data' => $view->render('load/index.phtml'),
            'menu' => $menu
        );
        $handler->sendToUser($user, $token);
    }

    public function content(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!isset($dataIn['m'])) {
            $dataIn['m'] = 3;
        }

        $db = $handler->getDb();

        $mGame = new Application_Model_Game(0, $db);
        $myGames = $mGame->getMyGames($user->parameters['playerId'], new Application_Model_PlayersInGame(0, $db), $dataIn['m']);

        $mPlayer = new Application_Model_Player($db);
        foreach ($myGames as &$game) {
            $mPlayersInGame = new Application_Model_PlayersInGame($game['gameId'], $db);
            $game['players'] = $mPlayersInGame->getGamePlayers();

            foreach (array_keys($game['players']) as $playerId) {
                if ($playerId == $user->parameters['playerId']) {
                    unset($game['players'][$playerId]);
                    break;
                }
            }

            $player = $mPlayer->getPlayer($game['turnPlayerId']);
            $player['name'] = trim($player['firstName'] . ' ' . $player['lastName']);
            unset($player['firstName']);
            unset($player['lastName']);

            $game['playerTurn'] = $player;
            $game['end'] = Coret_View_Helper_Formatuj::date($game['end'], 'Ymd H:i');
        }

        $token = array(
            'type' => 'load',
            'action' => 'content',
            'games' => $myGames
        );
        $handler->sendToUser($user, $token);
    }

    function delete(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!isset($dataIn['id'])) {
            return;
        }

        $db = $handler->getDb();

        new Cli_Model_SaveResults(new Cli_Model_Game($dataIn['id'], $db, new Cli_Model_TerrainTypes(array())), $db);

        $token = array(
            'type' => 'load',
            'action' => 'delete',
            'id' => $dataIn['id']
        );

        $handler->sendToUser($user, $token);
    }
}
