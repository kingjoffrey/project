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

        if (!Zend_Validate::is($user->parameters['gameId'], 'Digits') || !Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'No game ID or player ID. Not authorized.');
            return;
        }

        if ($user->parameters['timeLimit']) {
            if (time() - $user->parameters['begin'] > $user->parameters['timeLimit'] * 60) {
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
            if (time() - $user->parameters['turnStart'] > $user->parameters['turnTimeLimit'] * 60) {
                $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
                $mTurn = new Cli_Model_Turn($user, $db, $this);
                $user->parameters['turnStart'] = $mTurn->next($mGame->getTurnPlayerId());
                return;
            }
        }


        if ($dataIn['type'] == 'chat') {
            new Cli_Model_Chat($dataIn['msg'], $user, $db, $this);
            return;
        }

        Cli_Model_Database::addTokensIn($db, $user->parameters['gameId'], $user->parameters['playerId'], $dataIn);

        if ($dataIn['type'] == 'computer') {
            $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
            if (!$mGame->isGameMaster($user->parameters['playerId'])) {
                $this->sendError($user, 'Nie Twoja gra!');
                return;
            }

            $playerId = $mGame->getTurnPlayerId();

            $mPlayer = new Application_Model_Player($db);
            if (!$mPlayer->isComputer($playerId)) {
                $this->sendError($user, 'To (' . $playerId . ') nie komputer!');
                return;
            }

            $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
            if (!$mPlayersInGame->playerTurnActive($playerId)) {
                $mTurn = new Cli_Model_Turn($user, $db, $this);
                $mTurn->start($playerId, true);
            } else {
                if (Cli_Model_ComputerMainBlocks::handleHeroResurrection($user->parameters['gameId'], $playerId, $db, $this)) {
                    return;
                }

                $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
                $army = $mArmy2->getComputerArmyToMove($playerId);
                if (!empty($army['armyId'])) {
                    $mMain = new Cli_Model_ComputerMainBlocks($user->parameters['gameId'], $playerId, $db);
                    $token = $mMain->moveArmy(new Cli_Model_Army($army), $user, $this);
                    $token['type'] = 'computer';
                    $this->sendToChannel($db, $token, $user->parameters['gameId']);
                } else {
                    $mTurn = new Cli_Model_Turn($user, $db, $this);
                    $user->parameters['turnStart'] = $mTurn->next($playerId);
                }
            }

            return;
        }

        if ($dataIn['type'] == 'statistics') {
            new Cli_Model_Statistics($user, $db, $this);
            return;
        }

        if ($dataIn['type'] == 'tower') {
            $towerId = $dataIn['towerId'];
            if ($towerId === null) {
                $this->sendError($user, 'No "towerId"!');
                return;
            }

            $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
            $playerId = $mGame->getTurnPlayerId();
            // sprawdzić czy armia gracza jest w pobliżu wieży

            $mTowersInGame = new Application_Model_TowersInGame($user->parameters['gameId'], $db);
            if ($mTowersInGame->towerExists($towerId)) {
                $mTowersInGame->changeTowerOwner($towerId, $playerId);
            } else {
                $mTowersInGame->addTower($towerId, $playerId);
            }
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

            case 'split':
                $mSplitArmy = new Cli_Model_SplitArmy();
                $mSplitArmy->split($dataIn['armyId'], $dataIn['s'], $dataIn['h'], $user, $user->parameters['playerId'], $db, $this);
                break;

            case 'join':
                new Cli_Model_JoinArmy($dataIn['armyId'], $user, $db, $this);
                break;

            case 'fortify':
                $armyId = $dataIn['armyId'];
                if (empty($armyId)) {
                    $this->sendError($user, 'No "armyId"!');
                    return;
                }

                $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
                $mArmy2->fortify($armyId, $dataIn['fortify'], $user->parameters['playerId']);
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
                $date = $mTurn->next($user->parameters['playerId']);
                if ($date) {
                    $user->parameters['turnStart'] = $date;
                }
                break;

            case 'startTurn':
                $mTurn = new Cli_Model_Turn($user, $db, $this);
                $mTurn->start($user->parameters['playerId']);
                break;

            case 'production':
                new Cli_Model_Production($dataIn, $user, $db, $this);
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

            case 'bSequence':
                new Cli_Model_BattleSequence($dataIn, $user, $db, $this);
                break;
        }
    }

    public function onDisconnect(IWebSocketConnection $user)
    {
        if (Zend_Validate::is($user->parameters['gameId'], 'Digits') || Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $db = Cli_Model_Database::getDb();

            $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
            $mPlayersInGame->updatePlayerInGameWSSUId($user->parameters['playerId'], null);

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
