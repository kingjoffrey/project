<?php

class Cli_Model_BattleSequence
{

    public function __construct($data, $user, $db, $gameHandler)
    {
        $mBattleSequence = new Application_Model_BattleSequence($user->parameters['gameId'], $db);

        $result = 0;

        if ($data['attack']) {
            $attack = 'true';
        } else {
            $attack = 'false';
        }

        foreach ($data['sequence'] as $sequence => $unitId) {
            $result += $mBattleSequence->edit($user->parameters['playerId'], $unitId, $sequence, $attack);
        }

        if ($result != count($data['sequence'])) {
            $gameHandler->sendError($user, 'Error 1001');
            return;
        }

        $user->parameters['game']->setBattleSequence($data['sequence']);

        $token = array(
            'type' => 'bSequence',
            'sequence' => $data['sequence'],
            'attack' => $attack
        );

        $gameHandler->sendToUser($user, $db, $token, $user->parameters['gameId']);
    }

}