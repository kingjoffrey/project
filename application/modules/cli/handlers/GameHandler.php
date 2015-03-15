<?php

/**
 * This resource handler will respond to all messages sent to WebSockets channel "/game"
 *
 * All this class does is receiving data from browsers and sending responds back
 * Every client has his own copy of that class object
 * @author Bartosz Krzeszewski
 *
 */
class Cli_GameHandler extends Cli_WofHandler
{
    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg)
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
            return;
        }

        $gameId = $user->parameters['game']->getId();
        $playerId = $user->parameters['me']->getId();
        echo $playerId . '- Handler player ID' . "\n";

        // AUTHORIZATION
        if (!Zend_Validate::is($gameId, 'Digits') || !Zend_Validate::is($playerId, 'Digits')) {
            $this->sendError($user, 'No game ID or player ID. Not authorized.');
            return;
        }

        if ($dataIn['type'] == 'chat') {
            new Cli_Model_Chat($dataIn['msg'], $user, $db, $this);
            return;
        }

        if ($timeLimit = $user->parameters['game']->getTimeLimit()) {
            if (time() - $user->parameters['game']->getBegin() > $timeLimit * 600) {
                $mGame = new Application_Model_Game($gameId, $db);
                $mGame->endGame();
                new Cli_Model_SaveResults($gameId, $db, $this);
                return;
            }
        }

        if ($turnTimeLimit = $user->parameters['game']->getTurnTimeLimit()) {
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
            new Cli_Model_Statistics($gameId, $db, $this);
            return;
        }

        if (!$user->parameters['game']->isPlayerTurn($playerId)) {
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

    public function onDisconnect(IWebSocketConnection $user)
    {
        if ($user->parameters['game']) {
            $db = Cli_Model_Database::getDb();
            $gameId = $user->parameters['game']->getId();
            $playerId = $user->parameters['me']->getId();

            $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
            $mPlayersInGame->updateWSSUId($playerId, null);

            $playersInGameColors = Zend_Registry::get('playersInGameColors');

            $token = array(
                'type' => 'close',
                'color' => $playersInGameColors[$playerId]
            );

            $this->sendToChannel($db, $token, $gameId);

//            Game_Cli_Database::disconnectFromGame($gameId, $playerId, $db);
//            $this->update($gameId, $db);
        }

//        $this->say("[DEMO] {$user->getId()} disconnected");
    }

    public function sendToChannel($db, $token, $gameId, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ');
            print_r($token);
        }

        parent::sendToChannel($db, $token, $gameId, $debug);

        if ($token['type'] == 'chat') {
            return;
        }

        if (!Zend_Registry::get('config')->turnOffDatabaseLogging) {
            Cli_Model_Database::addTokensOut($db, $gameId, $token);
        }
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

        if (!Zend_Registry::get('config')->turnOffDatabaseLogging) {
            Cli_Model_Database::addTokensOut($db, $gameId, $token);
        }

        $this->send($user, Zend_Json::encode($token));
    }
}
