<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

class Cli_CommonHandler extends WebSocketUriHandler
{
    protected $_games = array();
    protected $_db;

    public function __construct($logger)
    {
        $this->_db = Cli_Model_Database::getDb();
        parent::__construct($logger);
    }

    public function getDb()
    {
        return $this->_db;
    }

    public function addGame($gameId)
    {
        $this->_games[$gameId] = new Cli_Model_Game($gameId, $this->_db);
    }

    public function removeGame($gameId)
    {
        $this->_games[$gameId] = null;
        unset($this->_games[$gameId]);
    }

    /**
     * @param $gameId
     * @return Cli_Model_Common
     */
    public function getGame($gameId)
    {
        if (isset($this->_games[$gameId])) {
            return $this->_games[$gameId];
        }
    }

    public function open($dataIn, WebSocketTransportInterface $user)
    {
        new Cli_Model_CommonOpen($dataIn, $user, $this);
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

        if ($dataIn['type'] == 'open') {
            $this->open($dataIn, $user);
            $game = Cli_CommonHandler::getGameFromUser($user);
            if ($game->isActive() && $game->getPlayers()->getPlayer($game->getPlayerColor($game->getTurnPlayerId()))->getComputer()) {
                new Cli_Model_Computer($user, $this);
            }
            return;
        }

        $game = Cli_CommonHandler::getGameFromUser($user);
        $gameId = $game->getId();
        $playerId = $user->parameters['me']->getId();

        // AUTHORIZATION
        if (!Zend_Validate::is($gameId, 'Digits') || !Zend_Validate::is($playerId, 'Digits')) {
            $this->sendError($user, 'No game ID or player ID. Not authorized.');
            return;
        }

        if ($dataIn['type'] == 'chat') {
            new Cli_Model_GameChat($dataIn['msg'], $user, $this);
            return;
        }

        if ($timeLimit = $game->getTimeLimit()) {
            if (time() - $game->getBegin() > $timeLimit * 600) {
                new Cli_Model_SaveResults($game, $this);
                return;
            }
        }

        if ($turnTimeLimit = $game->getTurnTimeLimit()) {
            $mTurn = new Application_Model_TurnHistory($gameId, $this->_db);
            $turn = $mTurn->getCurrentStatus();
            if (time() - strtotime($turn['date']) > $turnTimeLimit * 60) {
                $mGame = new Application_Model_Game($gameId, $this->_db);
                $mTurn = new Cli_Model_Turn($user, $this);
                $mTurn->next($mGame->getTurnPlayerId());
                return;
            }
        }

        if (!$config->turnOffDatabaseLogging) {
            Cli_Model_Database::addTokensIn($this->_db, $gameId, $playerId, $dataIn);
        }

        if ($dataIn['type'] == 'computer') {
            new Cli_Model_Computer($user, $this);
            return;
        }

        if ($dataIn['type'] == 'bSequence') {
            new Cli_Model_BattleSequenceHandler($dataIn, $user, $this);
            return;
        }

        if ($dataIn['type'] == 'production') {
            new Cli_Model_Production($dataIn, $user, $this);
            return;
        }

        if ($dataIn['type'] == 'statistics') {
            new Cli_Model_Statistics($user, $this);
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
                new Cli_Model_Move($dataIn, $user, $this);
                break;

            case 'split':
                new Cli_Model_SplitArmy($dataIn['armyId'], $dataIn['s'], $dataIn['h'], $playerId, $user, $this);
                break;

            case 'join':
                new Cli_Model_JoinArmy($dataIn['armyId'], $user, $this);
                break;

            case 'fortify':
                new Cli_Model_Fortify($dataIn['armyId'], $dataIn['fortify'], $user, $this);
                break;

            case 'disband':
                new Cli_Model_DisbandArmy($dataIn['armyId'], $user, $this);
                break;

            case 'resurrection':
                new Cli_Model_HeroResurrection($user, $this);
                break;

            case 'hire':
                new Cli_Model_HeroHire($user, $this);
                break;

            case 'ruin':
                new Cli_Model_SearchRuinHandler($dataIn['armyId'], $user, $this);
                break;

            case 'nextTurn':
                new Cli_Model_NextTurn($user, $this);
                break;

            case 'startTurn':
                new Cli_Model_StartTurn($playerId, $user, $this);
                break;

            case 'raze':
                new Cli_Model_CastleRaze($dataIn['armyId'], $user, $this);
                break;

            case 'defense':
                new Cli_Model_CastleBuildDefense($dataIn['castleId'], $user, $this);
                break;

            case 'inventoryDel':

                break;

            case 'surrender':
                new Cli_Model_Surrender($user, $this);
                break;
        }
    }

    public function onDisconnect(WebSocketTransportInterface $user)
    {
        $game = Cli_CommonHandler::getGameFromUser($user);
        if ($game) {
            $playerId = $user->parameters['me']->getId();
            $game->removeUser($playerId, $this->_db);

            if ($game->getUsers()) {
                $color = $game->getPlayerColor($playerId);
                $token = array(
                    'type' => 'close',
                    'color' => $color
                );

                $this->sendToChannel($game, $token);
            } else {
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

    /**
     * @param WebSocketTransportInterface $user
     * @return Cli_Model_Game
     */
    static public function getGameFromUser(Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        return $user->parameters['game'];
    }
}
