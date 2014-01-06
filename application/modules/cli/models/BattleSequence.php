<?php

class Cli_Model_BattleSequence
{

    public function __construct($data, $user, $db, $gameHandler)
    {
        $mBattleSequence = new Application_Model_BattleSequence($user->parameters['gameId'], $db);

        foreach ($data as $sequence => $unitId) {
            $mBattleSequence->add($user->parameters['playerId'], $unitId, $sequence);
        }

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'bSequence',
            'color' => $playersInGameColors[$user->parameters['playerId']]
        );

        $gameHandler->sendToUser($user, $db, $token, $user->parameters['gameId']);
    }

}