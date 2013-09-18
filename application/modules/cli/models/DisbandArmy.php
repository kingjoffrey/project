<?php

class Cli_Model_DisbandArmy
{

    public function __construct($armyId, $user, $db, $gameHandler)
    {
        if (empty($armyId)) {
            $gameHandler->sendError($user, 'Brak "armyId"!');
            return;
        }

        $destroyArmyResponse = Cli_Model_Database::destroyArmy($user->parameters['gameId'], $armyId, $user->parameters['playerId'], $db);

        if (!$destroyArmyResponse) {
            $gameHandler->sendError($user, 'Nie mogę usunąć armii!');
            return;
        }

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'disbandArmy',
            'armyId' => $armyId,
            'color' => $playersInGameColors($user->parameters['playerId'])
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}