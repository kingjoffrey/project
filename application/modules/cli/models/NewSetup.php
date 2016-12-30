<?php

class Cli_Model_NewSetup
{
    /**
     * Cli_Model_NewSetup constructor.
     * @param $dataIn
     * @param \Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Cli_NewHandler $handler
     * @throws Exception
     */
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_NewHandler $handler)
    {
        if (!isset($dataIn['gameMasterId'])) {
            throw new Exception('Brak "gameMasterId"');
        }

        $db = $handler->getDb();
        $new = Cli_Model_New::getNew($user);
        if ($dataIn['gameMasterId'] == $user->parameters['playerId']) {
            if (!$game = $new->getGame($dataIn['gameId'])) {
                $mGame = new Application_Model_Game($dataIn['gameId'], $db);
                $game = $mGame->getOpen($dataIn['gameMasterId']);
                $new->addGame($dataIn['gameId'], $game, $dataIn['name']);
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
    }
}