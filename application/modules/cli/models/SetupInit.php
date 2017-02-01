<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_SetupInit
{
    /**
     * Cli_Model_SetupInit constructor.
     * @param $dataIn
     * @param WebSocketTransportInterface $user
     * @param Cli_NewHandler $handler
     */
    public function __construct($dataIn, WebSocketTransportInterface $user, Cli_NewHandler $handler)
    {
        if (!isset($dataIn['gameId'])) {
            echo('Setup: brak "gameId"' . "\n");
            return;
        }

        $db = $handler->getDb();

        if (!($user->parameters['game'] = $handler->getSetupGame($dataIn['gameId']))) {
            echo 'not set' . "\n";
            $handler->addSetupGame($dataIn['gameId'], new SetupGame($dataIn['gameId'], $db));
            $user->parameters['game'] = $handler->getSetupGame($dataIn['gameId']);
        }

        $setupGame = SetupGame::getSetup($user);
        $setupGame->addUser($user->parameters['playerId'], $user, $db, $handler);

        foreach ($setupGame->getUsers() as $u) {
            $setupGame->update($u->parameters['playerId'], $handler);
        }

        $new = Cli_Model_New::getNew($user);
        if ($setupGame->getGameMasterId() == $user->parameters['playerId']) {
            $setupGame->setGameMasterName($user->parameters['name']);
            $new->addGame($dataIn['gameId'], $setupGame, $user->parameters['name']);
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