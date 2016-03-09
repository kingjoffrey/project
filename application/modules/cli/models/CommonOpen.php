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
            throw new Exception('Brak uprawnień!');
        }

        Zend_Registry::set('id_lang', $dataIn['langId']);

        if (!($user->parameters['game'] = $handler->getGame($dataIn['gameId']))) {
            echo 'not set' . "\n";
            $handler->addGame($dataIn['gameId']);
            $user->parameters['game'] = $handler->getGame($dataIn['gameId']);
        }

        $game = Cli_CommonHandler::getGameFromUser($user);
        $game->addUser($dataIn['playerId'], $user);
        $myColor = $game->getPlayerColor($dataIn['playerId']);
        $this->me($user, $myColor, $dataIn['playerId']);

        if (!$game->isActive()) {
            $token = array(
                'type' => 'end'
            );
            $handler->sendToChannel($game, $token);
            return;
        }

        $player = $game->getPlayers()->getPlayer($myColor);

        $token = $game->toArray();
        $token = array_merge($token, Cli_Model_Me::getMe($user)->toArray());
        $token['gold'] = $player->getGold();
        $token['bSequence'] = array('attack' => $player->getAttackSequence(), 'defense' => $player->getDefenceSequence());
        $token['type'] = 'open';

        $handler->sendToUser($user, $token);

        $token = array(
            'type' => 'online',
            'color' => $myColor
        );
        $handler->sendToChannel($game, $token);
    }

    public function me($user, $myColor, $playerId)
    {
        $user->parameters['me'] = new Cli_Model_Me($myColor, $playerId);
    }

}