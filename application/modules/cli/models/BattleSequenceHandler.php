<?php

class Cli_Model_BattleSequenceHandler
{
    public function __construct($data, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $game = Cli_Model_Game::getGame($user);
        $me = Cli_Model_Me::getMe($user);
        $player = $game->getPlayers()->getPlayer($me->getColor());
        $mBattleSequence = new Application_Model_BattleSequence($game->getId(), $db);
        $result = 0;

        if ($data['attack']) {
            $attack = 'true';
        } else {
            $attack = 'false';
        }

        foreach ($data['sequence'] as $sequence => $unitId) {
            $result += $mBattleSequence->edit($me->getId(), $unitId, $sequence, $attack);
        }

        if ($result != count($data['sequence'])) {
            $gameHandler->sendError($user, 'Error 1001');
            return;
        }

        $player->setBattleSequence($data['sequence']);

        $token = array(
            'type' => 'bSequence',
            'sequence' => $data['sequence'],
            'attack' => $attack
        );

        $gameHandler->sendToUser($user, $token);
    }
}