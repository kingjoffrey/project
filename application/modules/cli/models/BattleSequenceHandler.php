<?php

class Cli_Model_BattleSequenceHandler
{
    /**
     * Cli_Model_BattleSequenceHandler constructor.
     * @param $data
     * @param \Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param $handler
     */
    public function __construct($data, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        $game = Cli_CommonHandler::getGameFromUser($user);
        $me = Cli_Model_Me::getMe($user);
        $player = $game->getPlayers()->getPlayer($me->getColor());
        $db = $handler->getDb();
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
            $handler->sendError($user, 'Error 1001');
            return;
        }

        if ($attack) {
            $player->setAttackBattleSequence($data['sequence']);
        } else {
            $player->setDefenceBattleSequence($data['sequence']);
        }

        $token = array(
            'type' => 'bSequence',
            'sequence' => $data['sequence'],
            'attack' => $attack
        );

        $handler->sendToUser($user, $token);
    }
}