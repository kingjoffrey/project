<?php

class Cli_Model_Move
{

    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $handler)
    {
        if (!isset($dataIn['armyId'])) {
            $handler->sendError($user, 'No "armyId"!');
            return;
        }

        if (!isset($dataIn['x'])) {
            $handler->sendError($user, 'No "x"!');
            return;
        }

        if (!isset($dataIn['y'])) {
            $handler->sendError($user, 'No "y"!');
            return;
        }

        $attackerArmyId = $dataIn['armyId'];
        $x = $dataIn['x'];
        $y = $dataIn['y'];

        $playerId = $user->parameters['me']->getId();
        $game = Cli_Model_Game::getGame($user);

        if (!Zend_Validate::is($attackerArmyId, 'Digits') || !Zend_Validate::is($x, 'Digits') || !Zend_Validate::is($y, 'Digits')) {
            $handler->sendError($user, 'Niepoprawny format danych!');
            return;
        }

        $players = $game->getPlayers();
        $attackerColor = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($attackerColor);
        $army = $player->getArmies()->getArmy($attackerArmyId);

        if (empty($army)) {
            $handler->sendError($user, 'Brak armii o podanym ID! Odświerz przeglądarkę.');
            return;
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
                        $handler->sendError($user, 'Nie możesz zostawić armii na wodzie.');
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
                        $handler->sendError($user, 'Nie możesz zostawić armii w górach.');
                        return;
                    }
                }
                break;
        }

        try {
            $A_Star = new Cli_Model_Astar($army, $x, $y, $game);
            $path = $A_Star->path();
        } catch (Exception $e) {
            $l = new Coret_Model_Logger();
            $l->log($e);
            $handler->sendError($user, 'Wystąpił błąd podczas obliczania ścieżki');
            return;
        }

        $army->move($game, $path, $handler);
    }
}