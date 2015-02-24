<?php

/**
 * This resource handler will respond to all messages sent to WebSockets channel "/game"
 *
 * All this class does is receiving data from browsers and sending responds back
 * Every client has his own copy of that class object
 * @author Bartosz Krzeszewski
 *
 */
class Cli_GameHumansHandler extends Cli_WofHandler
{
    private $_game;

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
            $open = new Cli_Model_Open($dataIn, $user, $db, $this);
            $this->_game = $open->getGame();
            return;
        }

        $gameId = $this->_game->getId();
        $playerId = $this->_game->getMe()->getId();

        // AUTHORIZATION
        if (!Zend_Validate::is($gameId, 'Digits') || !Zend_Validate::is($playerId, 'Digits')) {
            $this->sendError($user, 'No game ID or player ID. Not authorized.');
            return;
        }

        if ($dataIn['type'] == 'chat') {
            new Cli_Model_Chat($dataIn['msg'], $user, $db, $this);
            return;
        }

        if ($timeLimit = $this->_game->getTimeLimit()) {
            if (time() - $this->_game->getBegin() > $timeLimit * 600) {
                $mGame = new Application_Model_Game($gameId, $db);
                $mGame->endGame();
                new Cli_Model_SaveResults($gameId, $db, $this);
                return;
            }
        }

        if ($turnTimeLimit = $this->_game->getTurnTimeLimit()) {
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
            new Cli_Model_Computer($user, $this->_game, $db, $this);
            return;
        }

        if ($dataIn['type'] == 'bSequence') {
            new Cli_Model_BattleSequence($dataIn, $user, $db, $this);
            return;
        }

        if ($dataIn['type'] == 'production') {
            new Cli_Model_Production($dataIn, $user, $this->_game, $db, $this);
            return;
        }

        if ($dataIn['type'] == 'statistics') {
            new Cli_Model_Statistics($gameId, $db, $this);
            return;
        }

        if (!$this->_game->isPlayerTurn($playerId)) {
            $this->sendError($user, 'Not your turn.');

            if ($config->exitOnErrors) {
                exit;
            }
            return;
        }

        switch ($dataIn['type']) {
            case 'move':
                new Cli_Model_Move($dataIn, $user, $this->_game, $db, $this);
                break;

            case 'split':
                new Cli_Model_SplitArmy($dataIn['armyId'], $dataIn['s'], $dataIn['h'], $playerId, $user, $this->_game, $db, $this);
                break;

            case 'join':
                new Cli_Model_JoinArmy($dataIn['armyId'], $user, $this->_game, $db, $this);
                break;

            case 'fortify':
                new Cli_Model_Fortify($dataIn['armyId'], $dataIn['fortify'], $user, $this->_game, $db, $this);
                break;

            case 'disband':
                new Cli_Model_DisbandArmy($dataIn['armyId'], $user, $this->_game, $db, $this);
                break;

            case 'resurrection':
                new Cli_Model_HeroResurrection($user, $this->_game, $db, $this);
                break;

            case 'hire':
                new Cli_Model_HeroHire($user, $this->_game,$db, $this);
                break;

            case 'ruin':
                new Cli_Model_SearchRuinHandler($dataIn['armyId'], $user, $this->_game, $db, $this);
                break;

            case 'nextTurn':
                new Cli_Model_NextTurn($this->_game, $db, $this);
                break;

            case 'startTurn':
                new Cli_Model_StartTurn($playerId, $user, $this->_game, $db, $this);
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
        if ($this->_game) {
            $db = Cli_Model_Database::getDb();
            $gameId = $this->_game->getId();
            $playerId = $this->_game->getMe()->getId();

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
