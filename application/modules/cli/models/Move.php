<?php

class Cli_Model_Move
{

    public function __construct($dataIn, IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
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

        $gameId = $game->getId();
        $me = $game->getMe();
        $playerId = $me->getId();

        if (!Zend_Validate::is($attackerArmyId, 'Digits') || !Zend_Validate::is($x, 'Digits') || !Zend_Validate::is($y, 'Digits')) {
            $gameHandler->sendError($user, 'Niepoprawny format danych!');
            return;
        }

        if (Zend_Validate::is($dataIn['s'], 'Digits') || Zend_Validate::is($dataIn['h'], 'Digits')) {
            $mSplitArmy = new Cli_Model_SplitArmy($dataIn['armyId'], $dataIn['s'], $dataIn['h'], $user, $playerId, $db, $gameHandler);
            $attackerArmyId = $mSplitArmy->getChildArmyId();
        }

        $players = $game->getPlayers();
        $army = $players->getPlayerArmy($game->getPlayerColor($playerId), $attackerArmyId);

        if (empty($army)) {
            $gameHandler->sendError($user, 'Brak armii o podanym ID! Odświerz przeglądarkę.');
            return;
        }

        $fields = $game->getFields();

        $defenderColor = null;
        $defender = null;
        $defenderId = null;
        $enemy = null;
        $attacker = null;
        $attackerColor = $game->getPlayerColor($playerId);
        $battleResult = null;
        $victory = false;
        $deletedIds = null;
        $castleId = null;
        $fight = false;

        $armyX = $army->getX();
        $armyY = $army->getY();

        $player = $players->getPlayer($attackerColor);

        switch ($fields->getType($armyX, $armyY)) {
            case 'w':
                $otherArmyId = $fields->isPlayerArmy($armyX, $armyY, $attackerColor);
                if ($otherArmyId) {
                    $otherArmy = $player->getArmy($otherArmyId);
                    if (!$otherArmy->canSwim() && !$otherArmy->canFly()) {
                        new Cli_Model_JoinArmy($otherArmyId, $user, $db, $gameHandler);
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
                        new Cli_Model_JoinArmy($otherArmyId, $user, $db, $gameHandler);
                        $gameHandler->sendError($user, 'Nie możesz zostawić armii w górach.');
                        return;
                    }
                }
                break;
        }

        /*
         * A* START
         */

        try {
            $A_Star = new Cli_Model_Astar($army, $x, $y, $fields, $attackerColor);
            $move = $army->calculateMovesSpend($A_Star->getPath($x . '_' . $y));
        } catch (Exception $e) {
            $l = new Coret_Model_Logger();
            $l->log($e);
            $gameHandler->sendError($user, 'Wystąpił błąd podczas obliczania ścieżki');
            return;
        }

        /*
         * A* END
         */

        if (!$move->end) {
            $token = array(
                'type' => 'move'
            );
            $gameHandler->sendToUser($user, $db, $token, $gameId);
            return;
        }

        if (Zend_Validate::is($castleId, 'Digits') && Application_Model_Board::isCastleField($move->end, $castlesSchema[$castleId]['position'])) { // enemy castle
//        if (Zend_Validate::is($castleId, 'Digits')) { // enemy castle (ZAMIENIĆ?)
            $fight = true;
            if ($defenderColor == 'neutral') {
                $enemy = new Cli_Model_Army(Cli_Model_Battle::getNeutralCastleGarrison($gameId, $db));
                $defenderId = 0;
            } else { // kolor wrogiego zamku sprawdzam dopiero wtedy gdy wiem, że armia ma na niego zasięg
                $defenderId = $mCastlesInGame->getPlayerIdByCastleId($castleId);
                $defenderColor = $playersInGameColors[$defenderId];
                $enemy = new Cli_Model_Army(Cli_Model_Army::getCastleGarrisonFromCastlePosition($castlesSchema[$castleId]['position'], $gameId, $db));
                $enemy->addCastleDefenseModifier($castleId, $gameId, $db);
                $enemy->setCombatDefenseModifiers();
            }
        } elseif ($defenderId && $move->x == $x && $move->y == $y) { // enemy army
            $fight = true;
            $defenderColor = $game->getPlayerColor($defenderId);
            $enemy = new Cli_Model_Army($mArmy2->getAllEnemyUnitsFromPosition($move->end, $playerId));
            $enemy->setCombatDefenseModifiers();
            $enemy->addTowerDefenseModifier();
        }

        /* ------------------------------------
         *
         * ZMIANY ZAPISUJĘ PONIZEJ TEJ LINII
         *
         * ------------------------------------ */

        if ($fight) {
            $battle = new Cli_Model_Battle($army, $enemy, Cli_Model_Army::getAttackSequence($gameId, $db, $playerId), Cli_Model_Army::getDefenceSequence($gameId, $db, $defenderId));
            $battle->fight();
            $battle->updateArmies($gameId, $db, $playerId, $defenderId);

            if (Zend_Validate::is($castleId, 'Digits')) {
                if ($defenderColor == 'neutral') {
                    $defender = $battle->getDefender();
                } else {
                    $defender = $mArmy2->getDefender($enemy->ids);
                }
            } else {
                $defender = $mArmy2->getDefender($enemy->ids);
            }

            if (!$battle->getDefender()) {
                if (Zend_Validate::is($castleId, 'Digits')) {

                    if ($defenderColor == 'neutral') {
                        $mCastlesInGame->addCastle($castleId, $playerId);
                    } else {
                        $mCastlesInGame->changeOwner($castlesSchema[$castleId], $playerId);
                    }
                }
                $army->updateArmyPosition($gameId, $move, $fields, $db);
                $victory = true;
//                foreach ($enemy['ids'] as $id) {
//                    $defender[]['armyId'] = $id;
//                }
            } else {
                $mArmy2->destroyArmy($army->id, $playerId);
//                $attacker = array(
//                    'armyId' => $attackerArmyId,
//                    'destroyed' => true
//                );
                if ($defenderColor == 'neutral') {
                    $defender = null;
                }
            }
            $battleResult = $battle->getResult();
        } else {
            $army->updateArmyPosition($gameId, $move, $fields, $db);
            $deletedIds = $player->joinArmiesAtPosition($army->getId(), $attackerColor, $db);
        }

        new Cli_Model_TowerHandler($playerId, $move->current, $game, $db, $gameHandler);

        $token = array(
            'type' => 'move',
            'attackerColor' => $attackerColor,
            'attackerArmy' => $army->toArray(),
            'defenderColor' => $defenderColor,
            'defenderArmy' => $defender,
            'battle' => $battleResult,
            'victory' => $victory,
            'x' => $x,
            'y' => $y,
            'castleId' => $castleId,
            'path' => $move->current,
            'oldArmyId' => $attackerArmyId,
            'deletedIds' => $deletedIds,
        );

        $gameHandler->sendToChannel($db, $token, $gameId);
    }

}