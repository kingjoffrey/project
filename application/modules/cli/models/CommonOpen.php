<?php

class Cli_Model_CommonOpen
{
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
        $db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);
        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            throw new Exception('Brak uprawnień!');
        }

        Zend_Registry::set('id_lang', $dataIn['langId']);

        if (!($user->parameters['game'] = $handler->getGame($dataIn['gameId']))) {
            echo 'not set' . "\n";
            $handler->addGame($dataIn['gameId'], new Cli_Model_Game($dataIn['gameId'], $db));
            $user->parameters['game'] = $handler->getGame($dataIn['gameId']);
        }

        $game = Cli_Model_Game::getGame($user);
        $game->addUser($dataIn['playerId'], $user);
        $myColor = $game->getPlayerColor($dataIn['playerId']);
        $user->parameters['me'] = new Cli_Model_Me($myColor, $dataIn['playerId']);

        if (!$game->isActive()) {
            $token = array(
                'type' => 'end'
            );
            $handler->sendToChannel($game, $token);
            return;
        }

        $player = $game->getPlayers()->getPlayer($myColor);

        $token = $game->toArray();
        $token['color'] = $myColor;
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
}