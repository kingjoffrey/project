<?php

class Cli_Model_BattleSequence
{

    public function __construct($data, $user, $db, $gameHandler)
    {
        $mBattleSequence = new Application_Model_BattleSequence($user->parameters['gameId'], $db);

        $result = 0;

        foreach ($data['sequence'] as $sequence => $unitId) {
            $result += $mBattleSequence->edit($user->parameters['playerId'], $unitId, $sequence);
        }

        if ($result != count($data['sequence'])) {
            $gameHandler->sendError($user, 'Error 1001');
            return;
        }

        $token = array(
            'type' => 'bSequence',
            'sequence' => $data['sequence']
        );

        $gameHandler->sendToUser($user, $db, $token, $user->parameters['gameId']);
    }

}