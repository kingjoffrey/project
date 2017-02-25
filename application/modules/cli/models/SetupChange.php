<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_SetupChange
{
    /**
     * Cli_Model_SetupChange constructor.
     * @param $dataIn
     * @param WebSocketTransportInterface $user
     * @param Cli_NewHandler $handler
     * @throws Exception
     */
    public function __construct($dataIn, WebSocketTransportInterface $user, Cli_NewHandler $handler)
    {
        $sideId = $dataIn['sideId'];

        if (empty($sideId)) {
            echo('Brak sideId!');
            return;
        }

        $setup = SetupGame::getSetup($user);

        if (empty($setup)) {
            return;
        }

        $playerId = $setup->getPlayerIdBySideId($sideId);

        if ($user->parameters['playerId'] == $playerId) { // unselect
            $setup->updatePlayerReady($user->parameters['playerId'], null);
            $setup->update($user->parameters['playerId'], $handler);
        } elseif (!$setup->isPlayer($sideId)) { // select
            $setup->updatePlayerReady($user->parameters['playerId'], $sideId);
            $setup->update($user->parameters['playerId'], $handler);
        } elseif ($setup->isGameMaster($user->parameters['playerId'])) { // kick
            $setup->updatePlayerReady($playerId, null);
            $setup->update($playerId, $handler);
        } else {
            $l = new Coret_Model_Logger('Cli_Model_SetupChange');
            $l->log('Błąd!');
        }
    }
}