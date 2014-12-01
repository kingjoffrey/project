<?php

/**
 * This resource handler will respond to all messages sent to /game on the socketserver below
 *
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Cli_GameHandler extends Cli_WofHandler
{

    public function __construct()
    {
        parent::__construct();
    }

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg)
    {

        $dataIn = Zend_Json::decode($msg->getData());

        if (Zend_Registry::get('config')->debug) {
            print_r('ZAPYTANIE ');
            print_r($dataIn);
        }

        $l = new Coret_Model_Logger();
        $l->log($dataIn);

        $db = Cli_Model_Database::getDb();

        if ($dataIn['type'] == 'open') {
            new Cli_Model_Open($dataIn, $user, $db, $this);
            return;
        }

//        if($dataIn['type'] == 'test') {
//            $open = new Cli_Model_Test($dataIn, $user, $db, $this);
//            return;
//        }

        if (!Zend_Validate::is($user->parameters['gameId'], 'Digits') || !Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'No game ID or player ID. Not authorized.');
            return;
        }

        if ($dataIn['type'] == 'chat') {
            new Cli_Model_Chat($dataIn['msg'], $user, $db, $this);
            return;
        }

        if ($timeLimit = $user->parameters['game']->getTimeLimit()) {
            if (time() - $user->parameters['begin'] > $timeLimit * 600) {
                $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
                $mGame->endGame();
                $mTurn = new Cli_Model_Turn($user, $db, $this);
                $mTurn->saveResults();
                $token = array(
                    'type' => 'end'
                );
                $this->sendToChannel($db, $token, $user->parameters['gameId']);
                return;
            }
        }

        if ($turnTimeLimit = $user->parameters['game']->getTurnTimeLimit()) {
            $mTurn = new Application_Model_TurnHistory($user->parameters['gameId'], $db);
            $turn = $mTurn->getCurrentStatus();
            if (time() - strtotime($turn['date']) > $turnTimeLimit * 60) {
                $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
                $mTurn = new Cli_Model_Turn($user, $db, $this);
                $mTurn->next($mGame->getTurnPlayerId());
                return;
            }
        }


        Cli_Model_Database::addTokensIn($db, $user->parameters['gameId'], $user->parameters['playerId'], $dataIn);

        if ($dataIn['type'] == 'computer') {
            new Cli_Model_Computer($user, $user->parameters['game'], $db, $this);
            return;
        }

        if ($dataIn['type'] == 'bSequence') {
            new Cli_Model_BattleSequence($dataIn, $user, $db, $this);
            return;
        }

        if ($dataIn['type'] == 'production') {
            new Cli_Model_Production($dataIn, $user, $user->parameters['game'], $db, $this);
            return;
        }

        if ($dataIn['type'] == 'statistics') {
            new Cli_Model_Statistics($user, $db, $this);
            return;
        }

        if (!$user->parameters['game']->isPlayerTurn($user->parameters['playerId'])) {
            $this->sendError($user, 'Not your turn.');

            if (Zend_Registry::get('config')->exitOnErrors) {
                exit;
            }
            return;
        }

        switch ($dataIn['type']) {
            case 'move':
                new Cli_Model_Move($dataIn, $user, $user->parameters['game'], $db, $this);
                break;

            case 'split':
                new Cli_Model_SplitArmy($dataIn['armyId'], $dataIn['s'], $dataIn['h'], $user, $user->parameters['playerId'], $db, $this);
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
                new Cli_Model_SearchRuin($dataIn['armyId'], $user, $db, $this);
                break;

            case 'nextTurn':
                new Cli_Model_NextTurn($user->parameters['playerId'], $user, $user->parameters['game'], $db, $this);
                break;

            case 'startTurn':
                new Cli_Model_StartTurn($user->parameters['playerId'], $user, $user->parameters['game'], $db, $this);
                break;

            case 'raze':
                new Cli_Model_CastleRaze($dataIn['armyId'], $user, $db, $this);
                break;

            case 'defense':
                new Cli_Model_CastleBuildDefense($dataIn['castleId'], $user, $db, $this);
                break;

            case 'inventoryAdd':
                new Cli_Model_InventoryAdd($dataIn['heroId'], $dataIn['artifactId'], $user, $db, $this);
                break;

            case 'inventoryDel':

                break;

            case 'surrender':
                new Cli_Model_Surrender($user, $db, $this);
                break;
        }
    }

    public function onDisconnect(IWebSocketConnection $user)
    {
        if ((isset($user->parameters['gameId']) && Zend_Validate::is($user->parameters['gameId'], 'Digits')) || (isset($user->parameters['playerId']) && Zend_Validate::is($user->parameters['playerId'], 'Digits'))) {
            $db = Cli_Model_Database::getDb();

            $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
            $mPlayersInGame->updateWSSUId($user->parameters['playerId'], null);

            $playersInGameColors = Zend_Registry::get('playersInGameColors');

            $token = array(
                'type' => 'close',
                'color' => $playersInGameColors[$user->parameters['playerId']]
            );

            $this->sendToChannel($db, $token, $user->parameters['gameId']);

//            Game_Cli_Database::disconnectFromGame($user->parameters['gameId'], $user->parameters['playerId'], $db);
//            $this->update($user->parameters['gameId'], $db);
        }

//        $this->say("[DEMO] {$user->getId()} disconnected");
    }

    public function sendToChannel($db, $token, $gameId, $debug = null)
    {
//        $l = new Coret_Model_Logger();
//        $l->log($token);
        parent::sendToChannel($db, $token, $gameId, $debug);

        if ($token['type'] == 'chat') {
            return;
        }

        Cli_Model_Database::addTokensOut($db, $gameId, $token);
    }

    /**
     * @param $user
     * @param $db
     * @param $token
     * @param $gameId
     * @param null $debug
     */
    public function sendToUser($user, $db, $token, $gameId, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ');
            print_r($token);
        }

        Cli_Model_Database::addTokensOut($db, $gameId, $token);

        $this->send($user, Zend_Json::encode($token));
    }
}
