<?php

class Cli_Model_Open
{
    /**
     * @param $dataIn
     * @param Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Zend_Db_Adapter_Pdo_Pgsql $db
     * @param Cli_GameHandler $gameHandler
     * @throws Exception
     */
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if (!isset($dataIn['gameId']) || !isset($dataIn['playerId']) || !isset($dataIn['langId'])) {
            $gameHandler->sendError($user, 'Brak "gameId" lub "playerId" lub "langId');
            return;
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($dataIn['gameId'], $db);

        if (!$mPlayersInGame->checkAccessKey($dataIn['playerId'], $dataIn['accessKey'], $db)) {
            $gameHandler->sendError($user, 'Brak uprawnień!');
            return;
        }

        Zend_Registry::set('id_lang', $dataIn['langId']);
        Zend_Registry::set('playersInGameColors', $mPlayersInGame->getAllColors());

        if (!($user->parameters['game'] = $gameHandler->getGame($dataIn['gameId']))) {
            echo 'not set' . "\n";
            $gameHandler->addGame($dataIn['gameId'], new Cli_Model_Game($dataIn['gameId'], $db));
            $user->parameters['game'] = $gameHandler->getGame($dataIn['gameId']);
        }

        $game = Cli_Model_Game::getGame($user);
        $game->addUser($dataIn['playerId'], $user, $mPlayersInGame);

        $myColor = $game->getPlayerColor($dataIn['playerId']);
        $user->parameters['me'] = new Cli_Model_Me($myColor, $dataIn['playerId']);
        $player = $game->getPlayers()->getPlayer($myColor);

        $token = $game->toArray();
        $token['color'] = $myColor;
        $token['gold'] = $player->getGold();
        $token['bSequence'] = array('attack' => $player->getAttackSequence(), 'defense' => $player->getDefenceSequence());
        $token['type'] = 'open';

        $gameHandler->sendToUser($user, $token);

        $token = array(
            'type' => 'online',
            'color' => $myColor
        );
        $gameHandler->sendToChannel($game, $token);
    }
}