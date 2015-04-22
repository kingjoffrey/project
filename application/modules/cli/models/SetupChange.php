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
        $db = $handler->getDb();
        $setup = Cli_Model_Setup::getSetup($user);
        $mapPlayerId = $dataIn['mapPlayerId'];

        if (empty($mapPlayerId)) {
            echo('Brak mapPlayerId!');
            return;
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($setup->getId(), $db);
        $mGame = new Application_Model_Game($setup->getId(), $db);

        if ($mPlayersInGame->getMapPlayerIdByPlayerId($setup->getId(), $user->parameters['playerId'], $db) == $mapPlayerId) { // unselect
            $mPlayersInGame->updatePlayerReady($user->parameters['playerId'], $mapPlayerId);
        } elseif (!$mPlayersInGame->isNoComputerColorInGame($mapPlayerId)) { // select
            if ($mPlayersInGame->isColorInGame($mapPlayerId)) {
                $mPlayersInGame->updatePlayerReady($mPlayersInGame->getPlayerIdByMapPlayerId($mapPlayerId), $mapPlayerId);
            }
            $mPlayersInGame->updatePlayerReady($user->parameters['playerId'], $mapPlayerId);
        } elseif ($mGame->isGameMaster($user->parameters['playerId'])) { // kick
            $mPlayersInGame->updatePlayerReady($mPlayersInGame->getPlayerIdByMapPlayerId($mapPlayerId), $mapPlayerId);
        } else {
            throw new Exception('Błąd!');
        }

        $setup->update($user->parameters['playerId'], $handler);
    }
}