<?php

class Cli_Model_Move
{

    public function __construct($dataIn, Cli_Model_Me $me, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if (!isset($dataIn['armyId'])) {
            $gameHandler->sendError($user, 'No "armyId"!');
            return;
        }

        if (!isset($dataIn['x'])) {
            $gameHandler->sendError($user, 'No "x"!');
            return;
        }

        if (!isset($dataIn['y'])) {
            $gameHandler->sendError($user, 'No "y"!');
            return;
        }

        $attackerArmyId = $dataIn['armyId'];
        $x = $dataIn['x'];
        $y = $dataIn['y'];

        $playerId = $me->getId();

        if (!Zend_Validate::is($attackerArmyId, 'Digits') || !Zend_Validate::is($x, 'Digits') || !Zend_Validate::is($y, 'Digits')) {
            $gameHandler->sendError($user, 'Niepoprawny format danych!');
            return;
        }

        if (Zend_Validate::is($dataIn['s'], 'Digits') || Zend_Validate::is($dataIn['h'], 'Digits')) {
            $sa = new Cli_Model_SplitArmy($dataIn['armyId'], $dataIn['s'], $dataIn['h'], $playerId, $user, $game, $db, $gameHandler);
            $attackerArmyId = $sa->getChildArmyId();
        }

        $players = $game->getPlayers();
        $attackerColor = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($attackerColor);
        $army = $player->getArmies()->getArmy($attackerArmyId);

        if (empty($army)) {
            $gameHandler->sendError($user, 'Brak armii o podanym ID! Odświerz przeglądarkę.');
            return;
        }

        $fields = $game->getFields();

        $armyX = $army->getX();
        $armyY = $army->getY();

        switch ($fields->getField($armyX, $armyY)->getType()) {
            case 'w':
                $otherArmyId = $fields->isPlayerArmy($armyX, $armyY, $attackerColor);
                if ($otherArmyId) {
                    $otherArmy = $player->getArmy($otherArmyId);
                    if (!$otherArmy->canSwim() && !$otherArmy->canFly()) {
                        new Cli_Model_JoinArmy($otherArmyId, $user, $game, $db, $gameHandler);
                        $gameHandler->sendError($user, 'Nie możesz zostawić armii na wodzie.');
                        return;
                    }
                }
                break;
            case'M':
                $otherArmyId = $fields->isPlayerArmy($armyX, $armyY, $attackerColor);
                if ($otherArmyId) {
                    $otherArmy = $player->getArmy($otherArmyId);
                    if (!$otherArmy->canFly()) {
                        new Cli_Model_JoinArmy($otherArmyId, $user, $game, $db, $gameHandler);
                        $gameHandler->sendError($user, 'Nie możesz zostawić armii w górach.');
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
            $gameHandler->sendError($user, 'Wystąpił błąd podczas obliczania ścieżki');
            return;
        }

        $army->move($game, $path, $db, $gameHandler);
    }
}