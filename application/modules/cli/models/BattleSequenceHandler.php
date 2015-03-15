<?php

class Cli_Model_BattleSequenceHandler
{
    public function __construct($data, IWebSocketConnection $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        $game = $this->getGame($user);
        $mBattleSequence = new Application_Model_BattleSequence($game->getId(), $db);
        $result = 0;

        if ($data['attack']) {
            $attack = 'true';
        } else {
            $attack = 'false';
        }

        foreach ($data['sequence'] as $sequence => $unitId) {
            $result += $mBattleSequence->edit($user->parameters['me']->getId(), $unitId, $sequence, $attack);
        }

        if ($result != count($data['sequence'])) {
            $gameHandler->sendError($user, 'Error 1001');
            return;
        }

        $game->setBattleSequence($data['sequence']);

        $token = array(
            'type' => 'bSequence',
            'sequence' => $data['sequence'],
            'attack' => $attack
        );

        $gameHandler->sendToUser($user, $db, $token, $game->getId());
    }

    /**
     * @param IWebSocketConnection $user
     * @return Cli_Model_Game
     */
    private function getGame(IWebSocketConnection $user)
    {
        return $user->parameters['game'];
    }
}