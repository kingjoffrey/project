<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

/**
 * This resource handler will respond to all messages sent to WebSockets channel "/game"
 *
 * All this class does is receiving data from browsers and sending responds back
 * Every client has his own copy of that class object
 * @author Bartosz Krzeszewski
 *
 */
class Cli_GameHandler extends WebSocketUriHandler
{
    protected $_game = array();

    public function getGame($gameId)
    {
        if (isset($this->_game[$gameId])) {
            return $this->_game[$gameId];
        }
    }

    public function addGame($gameId, $game)
    {
        $this->_game[$gameId] = $game;
    }

    public function removeGame($gameId)
    {
        $this->_game[$gameId] = null;
        unset($this->_game[$gameId]);
    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $config = Zend_Registry::get('config');
        $dataIn = Zend_Json::decode($msg->getData());

        if ($config->debug) {
            print_r('ZAPYTANIE ');
            print_r($dataIn);
        }

        $l = new Coret_Model_Logger();
        $l->log($dataIn);

        $db = Cli_Model_Database::getDb();

        if ($dataIn['type'] == 'open') {
            new Cli_Model_Open($dataIn, $user, $db, $this);
            $game = Cli_Model_Game::getGame($user);
            if ($game->isActive() && $game->getPlayers()->getPlayer($game->getPlayerColor($game->getTurnPlayerId()))->getComputer()) {
                new Cli_Model_Computer($user, $db, $this);
            }
            return;
        }

        $game = Cli_Model_Game::getGame($user);
        $gameId = $game->getId();
        $playerId = $user->parameters['me']->getId();
//        echo $playerId . '- Handler player ID' . "\n";

        // AUTHORIZATION
        if (!Zend_Validate::is($gameId, 'Digits') || !Zend_Validate::is($playerId, 'Digits')) {
            $this->sendError($user, 'No game ID or player ID. Not authorized.');
            return;
        }

        if ($dataIn['type'] == 'chat') {
            new Cli_Model_Chat($dataIn['msg'], $user, $db, $this);
            return;
        }

        if ($timeLimit = $game->getTimeLimit()) {
            if (time() - $game->getBegin() > $timeLimit * 600) {
                new Cli_Model_SaveResults($game, $db, $this);
                return;
            }
        }

        if ($turnTimeLimit = $game->getTurnTimeLimit()) {
            $mTurn = new Application_Model_TurnHistory($gameId, $db);
            $turn = $mTurn->getCurrentStatus();
            if (time() - strtotime($turn['date']) > $turnTimeLimit * 60) {
                $mGame = new Application_Model_Game($gameId, $db);
                $mTurn = new Cli_Model_Turn($user, $db, $this);
                $mTurn->next($mGame->getTurnPlayerId());
                return;
            }
        }

        if (!$config->turnOffDatabaseLogging) {
            Cli_Model_Database::addTokensIn($db, $gameId, $playerId, $dataIn);
        }

        if ($dataIn['type'] == 'computer') {
            new Cli_Model_Computer($user, $db, $this);
            return;
        }

        if ($dataIn['type'] == 'bSequence') {
            new Cli_Model_BattleSequenceHandler($dataIn, $user, $db, $this);
            return;
        }

        if ($dataIn['type'] == 'production') {
            new Cli_Model_Production($dataIn, $user, $db, $this);
            return;
        }

        if ($dataIn['type'] == 'statistics') {
            new Cli_Model_Statistics($user, $db, $this);
            return;
        }

        if (!$game->isPlayerTurn($playerId)) {
            $this->sendError($user, 'Not your turn.');

            if ($config->exitOnErrors) {
                exit;
            }
            return;
        }

        switch ($dataIn['type']) {
            case 'move':
                new Cli_Model_Move($dataIn, $user, $db, $this);
                break;

            case 'split':
                new Cli_Model_SplitArmy($dataIn['armyId'], $dataIn['s'], $dataIn['h'], $playerId, $user, $db, $this);
                break;

            case 'join':
                new Cli_Model_JoinArmy($dataIn['armyId'], $user, $db, $this);
                break;

            case 'fortify':
                new Cli_Model_Fortify($dataIn['armyId'], $dataIn['fortify'], $user, $db, $this);
                break;

            case 'disband':
                new Cli_Model_DisbandArmy($dataIn['armyId'], $user, $db, $this);
                break;

            case 'resurrection':
                new Cli_Model_HeroResurrection($user, $db, $this);
                break;

            case 'hire':
                new Cli_Model_HeroHire($user, $db, $this);
                break;

            case 'ruin':
                new Cli_Model_SearchRuinHandler($dataIn['armyId'], $user, $db, $this);
                break;

            case 'nextTurn':
                new Cli_Model_NextTurn($user, $db, $this);
                break;

            case 'startTurn':
                new Cli_Model_StartTurn($playerId, $user, $db, $this);
                break;

            case 'raze':
                new Cli_Model_CastleRaze($dataIn['armyId'], $user, $db, $this);
                break;

            case 'defense':
                new Cli_Model_CastleBuildDefense($dataIn['castleId'], $user, $db, $this);
                break;

            case 'inventoryDel':

                break;

            case 'surrender':
                new Cli_Model_Surrender($user, $db, $this);
                break;
        }
    }

    public function onDisconnect(WebSocketTransportInterface $user)
    {
        $game = Cli_Model_Game::getGame($user);
        if ($game) {
            $playerId = $user->parameters['me']->getId();
            $color = $game->getPlayerColor($playerId);

            $game->removeUser($playerId, Cli_Model_Database::getDb());

            $token = array(
                'type' => 'close',
                'color' => $color
            );

            $this->sendToChannel($game, $token);

            if (!$game->getUsers()) {
                $this->removeGame($game->getId());
            }
        }
    }

    /**
     * @param Cli_Model_Game $game
     * @param $token
     * @param null $debug
     * @throws Zend_Exception
     */
    public function sendToChannel(Cli_Model_Game $game, $token, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }

        foreach ($game->getUsers() as $user) {
            $this->sendToUser($user, $token);
        }
    }

    /**
     * @param $user
     * @param $msg
     */
    public function sendError($user, $msg)
    {
        $token = array(
            'type' => 'error',
            'msg' => $msg
        );

        $this->sendToUser($user, $token);
    }

    /**
     * @param $user
     * @param $token
     * @param null $debug
     */
    public function sendToUser(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $token, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ');
            print_r($token);
        }

        $user->sendString(Zend_Json::encode($token));
    }
}
