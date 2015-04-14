<?php

class Cli_Model_GameOpen
{
    /**
     * @param $dataIn
     * @param Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Zend_Db_Adapter_Pdo_Pgsql $db
     * @param Cli_GameHandler $handler
     * @throws Exception
     */
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_GameHandler $handler)
    {
        if (!isset($dataIn['gameId']) || !isset($dataIn['playerId']) || !isset($dataIn['langId'])) {
            throw new Exception('Brak "gameId" lub "playerId" lub "langId');
            return;
        }
        $db = $handler->getDb();
        $mPlayersInGame = new Application_Model_PlayersInGame($dataIn['gameId'], $db);

        if (!$mPlayersInGame->checkAccessKey($dataIn['playerId'], $dataIn['accessKey'], $db)) {
            throw new Exception($user, 'Brak uprawnień!');
            return;
        }

        Zend_Registry::set('id_lang', $dataIn['langId']);

        if (!($user->parameters['game'] = $handler->getGame($dataIn['gameId']))) {
            echo 'not set' . "\n";
            $handler->addGame($dataIn['gameId'], new Cli_Model_Game($dataIn['gameId'], $mPlayersInGame->getAllColors(), $db));
            $user->parameters['game'] = $handler->getGame($dataIn['gameId']);
        }

        $game = Cli_Model_Game::getGame($user);
        $game->addUser($dataIn['playerId'], $user, $mPlayersInGame, $handler);
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