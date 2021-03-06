<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_MultiplayerInit
{
    /**
     * Cli_Model_SetupInit constructor.
     * @param $dataIn
     * @param WebSocketTransportInterface $user
     * @param Cli_OpenGamesHandler $handler
     */
    public function __construct($dataIn, WebSocketTransportInterface $user, Cli_OpenGamesHandler $handler)
    {
        if (!isset($dataIn['gameId'])) {
            echo('Setup: brak "gameId"' . "\n");
            return;
        }

        $db = $handler->getDb();

        if (!($user->parameters['game'] = $handler->getSetupGame($dataIn['gameId']))) {
            echo 'not set' . "\n";
            $handler->addSetupGame($dataIn['gameId'], new OpenGame($dataIn['gameId'], $db));
            $user->parameters['game'] = $handler->getSetupGame($dataIn['gameId']);
        }

        $openGame = OpenGame::getGame($user);
        $openGame->addUser($user->parameters['playerId'], $user, $db, $handler);

        foreach ($openGame->getUsers() as $u) {
            $openGame->update($u->parameters['playerId'], $handler);
        }

        $new = Cli_Model_New::getNew($user);
        if ($openGame->getGameMasterId() == $user->parameters['playerId']) {
            $openGame->setGameMasterName($user->parameters['name']);
            $new->addGame($dataIn['gameId'], $openGame, $user->parameters['name']);
            $new->getGame($dataIn['gameId'])->addPlayer($user->parameters['playerId']);
            $token = array(
                'type' => 'addGame',
                'game' => $new->getGame($dataIn['gameId'])->toArray()
            );
        } else {
            $new->getGame($dataIn['gameId'])->addPlayer($user->parameters['playerId']);
            $token = array(
                'type' => 'addPlayer',
                'playerId' => $user->parameters['playerId'],
                'gameId' => $dataIn['gameId']
            );
        }
        $user->parameters['gameId'] = $dataIn['gameId'];
        $handler->sendToChannelExceptPlayers($new, $token);
    }
}