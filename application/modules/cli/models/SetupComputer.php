<?php

class Cli_Model_SetupComputer
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

        $mGame = new Application_Model_Game($setup->getId(), $db);
        if (!$mGame->isGameMaster($user->parameters['playerId'])) {
            echo('Brak uprawnień!');
            return;
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($setup->getId(), $db);

        if ($mPlayersInGame->isColorInGame($mapPlayerId)) {
            echo('Ten kolor jest już w grze!');
            return;
        }

        $playerId = $mPlayersInGame->getComputerPlayerId();

        if (!$playerId) {
            $mPlayer = new Application_Model_Player($db);
            $playerId = $mPlayer->createComputerPlayer();

            $mHero = new Application_Model_Hero($playerId, $db);
            $mHero->createHero();
        }

        if (!$mPlayersInGame->isPlayerInGame($playerId)) {
            $mPlayersInGame->joinGame($playerId);
        }
        $mPlayersInGame->updatePlayerReady($playerId, $mapPlayerId);

        $setup->update($user->parameters['playerId'], $handler);
    }
}