<?php

class Cli_Model_SetupChange
{
    /**
     * @param $dataIn
     * @param \Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Cli_SetupHandler $handler
     * @throws Exception
     */
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_SetupHandler $handler)
    {
        $mapPlayerId = $dataIn['mapPlayerId'];

        if (empty($mapPlayerId)) {
            echo('Brak mapPlayerId!');
            return;
        }

        $setup = Cli_Model_Setup::getSetup($user);
        $playerId = $setup->getPlayerIdByMapPlayerId($mapPlayerId);

        if ($user->parameters['playerId'] == $playerId) { // unselect
            $setup->updatePlayerReady($user->parameters['playerId'], null);
            $setup->update($user->parameters['playerId'], $handler);
        } elseif (!$setup->isPlayer($mapPlayerId)) { // select
            $setup->updatePlayerReady($user->parameters['playerId'], $mapPlayerId);
            $setup->update($user->parameters['playerId'], $handler);
        } elseif ($setup->isGameMaster($user->parameters['playerId'])) { // kick
            $setup->updatePlayerReady($playerId, null);
            $setup->update($playerId, $handler);
        } else {
            throw new Exception('Błąd!');
        }
    }
}