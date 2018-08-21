<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_MultiplayerChange
{
    /**
     * Cli_Model_SetupChange constructor.
     * @param $dataIn
     * @param WebSocketTransportInterface $user
     * @param Cli_OpenGamesHandler $handler
     * @throws Exception
     */
    public function __construct($dataIn, WebSocketTransportInterface $user, Cli_OpenGamesHandler $handler)
    {
        $sideId = $dataIn['sideId'];

        if (empty($sideId)) {
            echo('Brak sideId!');
            return;
        }

        $openGame = OpenGame::getGame($user);

        if (empty($openGame)) {
            return;
        }

        $playerId = $openGame->getPlayerIdBySideId($sideId);

        if ($user->parameters['playerId'] == $playerId) { // unselect
            $openGame->updatePlayerReady($user->parameters['playerId'], null);
            $openGame->update($user->parameters['playerId'], $handler);
        } elseif (!$openGame->isPlayer($sideId)) { // select
            $openGame->updatePlayerReady($user->parameters['playerId'], $sideId);
            $openGame->update($user->parameters['playerId'], $handler);
        } elseif ($openGame->isGameMaster($user->parameters['playerId'])) { // kick
            $openGame->updatePlayerReady($playerId, null);
            $openGame->update($playerId, $handler);
        } else {
            $l = new Coret_Model_Logger(get_class($this));
            $l->log('Błąd!');
        }
    }
}