<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_Move
{
    /**
     * @param array $dataIn
     * @param WebSocketTransportInterface $user
     * @param Cli_CommonHandler $handler
     */
    public function __construct($dataIn, WebSocketTransportInterface $user, $handler)
    {
        if (!isset($dataIn['armyId'])) {
            $l = new Coret_Model_Logger('Cli_Model_Move');
            $l->log('No "armyId"!');
            $handler->sendError($user, 'Error 1014');
            return;
        }

        if (!isset($dataIn['x'])) {
            $l = new Coret_Model_Logger('Cli_Model_Move');
            $l->log('No "x"!');
            $handler->sendError($user, 'Error 1015');
            return;
        }

        if (!isset($dataIn['y'])) {
            $l = new Coret_Model_Logger('Cli_Model_Move');
            $l->log('No "y"!');
            $handler->sendError($user, 'Error 1016');
            return;
        }

        $attackerArmyId = $dataIn['armyId'];
        $x = $dataIn['x'];
        $y = $dataIn['y'];

        $playerId = $user->parameters['me']->getId();
        $game = $handler->getGame();

        if (!Zend_Validate::is($attackerArmyId, 'Digits') || !Zend_Validate::is($x, 'Digits') || !Zend_Validate::is($y, 'Digits')) {
            $l = new Coret_Model_Logger('Cli_Model_Move');
            $l->log('Niepoprawny format danych!');
            $handler->sendError($user, 'Error 1017');
            return;
        }

        $players = $game->getPlayers();
        $attackerColor = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($attackerColor);
        $army = $player->getArmies()->getArmy($attackerArmyId);

        if (empty($army)) {
            $l = new Coret_Model_Logger('Cli_Model_Move');
            $l->log('Brak armii o podanym ID!');
            $handler->sendError($user, 'Error 1018');
            return;
        }

        if ($army->canSwim()) {
            $random = rand(1, 10);

            if ($random == 10) {
                $db = $handler->getDb();
                $player->getArmies()->removeArmy($attackerArmyId, $game, $db);

                $token = array(
                    'color' => $attackerColor,
                    'id' => $attackerArmyId,
                    'type' => 'bulbul'
                );
                $handler->sendToChannel($token);

                return;
            }
        }

        $fields = $game->getFields();

        $armyX = $army->getX();
        $armyY = $army->getY();

        switch ($fields->getField($armyX, $armyY)->getType()) {
            case 'w':
                $otherArmyId = $fields->isPlayerArmy($armyX, $armyY, $attackerColor);
                if ($otherArmyId) {
                    $otherArmy = $player->getArmies()->getArmy($otherArmyId);
                    if (!$otherArmy->canSwim() && !$otherArmy->canFly()) {
                        new Cli_Model_JoinArmy($otherArmyId, $user, $handler);
                        $l = new Coret_Model_Logger('Cli_Model_Move');
                        $l->log('Nie możesz zostawić armii na wodzie.');
                        $handler->sendError($user, 'Error 1019');
                        return;
                    }
                }
                break;
            case'M':
                $otherArmyId = $fields->isPlayerArmy($armyX, $armyY, $attackerColor);
                if ($otherArmyId) {
                    $otherArmy = $player->getArmies()->getArmy($otherArmyId);
                    if (!$otherArmy->canFly()) {
                        new Cli_Model_JoinArmy($otherArmyId, $user, $handler);
                        $l = new Coret_Model_Logger('Cli_Model_Move');
                        $l->log('Nie możesz zostawić armii w górach.');
                        $handler->sendError($user, 'Error 1020');
                        return;
                    }
                }
                break;
        }

        try {
            $A_Star = new Cli_Model_Astar($army, $x, $y, $game);
            $path = $A_Star->path();
        } catch (Exception $e) {
            $l = new Coret_Model_Logger('Cli_Model_Move');
            $l->log($e);
            $handler->sendError($user, 'Error 1021');
            return;
        }

        $army->move($game, $path, $handler, $user);
    }
}
