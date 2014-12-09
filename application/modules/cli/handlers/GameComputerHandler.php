<?php

/**
 * This resource handler will control computer players and will be activated by messages sent from WebSockets channel "/computer"
 *
 * All this class does is receiving data from browsers and sending responds back
 * Every client has his own copy of that class object
 * @author Bartosz Krzeszewski
 *
 */
class Cli_GameComputerHandler extends Cli_WofHandler
{

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

        $playerId = $game->getTurnPlayerId();
        $players = $game->getPlayers();
        $color = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($color);

        if (!$player->getTurnActive()) {
            $l->log('START TURY');
            new Cli_Model_StartTurn($playerId, $user, $game, $db, $gameHandler);
            return;
        }

        if (!$player->getComputer()) {
            echo 'To (' . $playerId . ') nie komputer!' . "\n";
//                $this->sendError($user, 'To (' . $playerId . ') nie komputer!');
            return;
        }

        if (Cli_Model_ComputerHeroResurrection::handle($playerId, $game, $db, $gameHandler)) {
            return;
        }

        if ($army = $player->getArmies()->getComputerArmyToMove()) {
            new Cli_Model_ComputerMove($army, $user, $game, $db, $gameHandler);
        } else {
            $l->log('NASTĘPNA TURA');
            new Cli_Model_NextTurn($game, $db, $gameHandler);
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
