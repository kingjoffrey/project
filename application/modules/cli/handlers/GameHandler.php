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
            $open = new Cli_Model_Open($dataIn, $user, $db, $this);
            $user->parameters = $open->getParameters();
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

        if ($user->parameters['timeLimit']) {
            if (time() - $user->parameters['begin'] > $user->parameters['timeLimit'] * 600) {
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

        if ($user->parameters['turnTimeLimit']) {
            $mTurn = new Application_Model_TurnHistory($user->parameters['gameId'], $db);
            $turn = $mTurn->getCurrentStatus();
            if (time() - strtotime($turn['date']) > $user->parameters['turnTimeLimit'] * 60) {
                $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
                $mTurn = new Cli_Model_Turn($user, $db, $this);
                $mTurn->next($mGame->getTurnPlayerId());
                return;
            }
        }


        Cli_Model_Database::addTokensIn($db, $user->parameters['gameId'], $user->parameters['playerId'], $dataIn);

        if ($dataIn['type'] == 'computer') {
            $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
            if (!$mGame->isGameMaster($user->parameters['playerId'])) {
                $this->sendError($user, 'Nie Twoja gra!');
                return;
            }

            $playerId = $mGame->getTurnPlayerId();

            $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);

            if (!$mPlayersInGame->playerTurnActive($playerId)) {
                $l->log('START TURY');
                $mTurn = new Cli_Model_Turn($user, $db, $this);
                $mTurn->start($playerId, true);
                return;
            }

            $mPlayer = new Application_Model_Player($db);
            if (!$mPlayer->isComputer($playerId)) {
                echo 'To (' . $playerId . ') nie komputer!' . "\n";
//                $this->sendError($user, 'To (' . $playerId . ') nie komputer!');
                return;
            }

            if (Cli_Model_ComputerHeroResurrection::handle($user->parameters['gameId'], $playerId, $db, $this)) {
                return;
            }

            $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
            $army = $mArmy2->getComputerArmyToMove($playerId);

            if (!empty($army['armyId'])) {
                $mMain = new Cli_Model_ComputerMain($user, $playerId, $db, $this);
                $user = $mMain->move(new Cli_Model_Army($army));
            } else {
                $l->log('NASTĘPNA TURA');
                $mTurn = new Cli_Model_Turn($user, $db, $this);
                $mTurn->next($playerId);
            }

            return;
        }

        if ($dataIn['type'] == 'bSequence') {
            new Cli_Model_BattleSequence($dataIn, $user, $db, $this);
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

        if (!isset($mGame)) {
            $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
        }

        if (!$mGame->isPlayerTurn($user->parameters['playerId'])) {
            $this->sendError($user, 'Not your turn.');

            if (Zend_Registry::get('config')->exitOnErrors) {
                exit;
            }
            return;
        }

        switch ($dataIn['type']) {
            case 'move':
                new Cli_Model_Move($dataIn, $user, $db, $this);
                break;

            case 'tower':
                new Cli_Model_Tower($dataIn['towerId'], $user, $db, $this);
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
                $mTurn = new Cli_Model_Turn($user, $db, $this);
                $mTurn->next($user->parameters['playerId']);
                break;

            case 'startTurn':
                $mTurn = new Cli_Model_Turn($user, $db, $this);
                $mTurn->start($user->parameters['playerId']);
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
        if (Zend_Validate::is($user->parameters['gameId'], 'Digits') || Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
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
