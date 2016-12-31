<?php

class Cli_Model_SetupInit
{
    /**
     * Cli_Model_SetupInit constructor.
     * @param $dataIn
     * @param \Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Cli_NewHandler $handler
     */
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_NewHandler $handler)
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

        $setup = SetupGame::getSetup($user);
        $setup->addUser($user->parameters['playerId'], $user, $db, $handler);

        foreach ($setup->getUsers() as $u) {
            $setup->update($u->parameters['playerId'], $handler);
        }

        $new = Cli_Model_New::getNew($user);
        $gameMasterId = $setup->getGameMasterId();
        if ($gameMasterId == $user->parameters['playerId']) {
            if (!$game = $new->getGame($dataIn['gameId'])) {
                $new->addGame($dataIn['gameId'], $setup->toArray(), $user->parameters['name']);
            }

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

//        $token = array(
//            'type' => 'setup'
//        );
//
//        $handler->sendToUser($user, $token);
    }
}