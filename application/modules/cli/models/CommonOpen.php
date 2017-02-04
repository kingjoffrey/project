<?php

class Cli_Model_CommonOpen
{
    protected $_db;

    /**
     * @param $dataIn
     * @param \Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Cli_CommonHandler $handler
     * @throws Exception
     */
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_CommonHandler $handler)
    {
        if (!isset($dataIn['gameId']) || !isset($dataIn['playerId']) || !isset($dataIn['langId'])) {
            throw new Exception('Brak "gameId" lub "playerId" lub "langId');
            return;
        }
        $this->_db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $this->_db);
        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $this->_db)) {
            echo ('Brak uprawnień (playerId=' . $dataIn['playerId'] . ')') . "\n";
            return;
        }

        Zend_Registry::set('id_lang', $dataIn['langId']);

        if (!($game = $handler->getGame())) {
            echo 'not set' . "\n";
            $handler->initGame($dataIn['gameId']);
            $game = $handler->getGame();
        }

        $user->parameters['game'] = $game;
        $game->addUser($dataIn['playerId'], $user);
        $myColor = $game->getPlayerColor($dataIn['playerId']);
        $this->handleMe($user, $myColor, $dataIn['playerId']);

        if (!$game->isActive()) {
            $token = array(
                'type' => 'end'
            );
            $handler->sendToChannel($token);
            return;
        }

        $player = $game->getPlayers()->getPlayer($myColor);

        $token = $game->toArray();
        $token = array_merge($token, Cli_Model_Me::getMe($user)->toArray());
        $token['gold'] = $player->getGold();
        $token['bSequence'] = array('attack' => $player->getAttackSequence(), 'defense' => $player->getDefenceSequence());
        $token['type'] = 'open';

        $handler->sendToUser($user, $token);
    }

    public function handleMe($user, $myColor, $playerId)
    {
        $me = new Cli_Model_Me($myColor, $playerId);
        if (empty($me)) {
            throw new Exception('Cli_Model_Me me object is empty');
        }
        $user->parameters['me'] = $me;
    }
}