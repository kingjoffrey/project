<?php

class Cli_Model_Move
{

    public function __construct($dataIn, $user, $db, $gameHandler)
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

        if (!Zend_Validate::is($attackerArmyId, 'Digits') || !Zend_Validate::is($x, 'Digits') || !Zend_Validate::is($y, 'Digits')) {
            $gameHandler->sendError($user, 'Niepoprawny format danych!');
            return;
        }

        if (Zend_Validate::is($dataIn['s'], 'Digits') || Zend_Validate::is($dataIn['h'], 'Digits')) {
            $mSplitArmy = new Cli_Model_SplitArmy($dataIn['armyId'], $dataIn['s'], $dataIn['h'], $user, $user->parameters['playerId'], $db, $gameHandler);
            $attackerArmyId = $mSplitArmy->getChildArmyId();
        }

        $defenderColor = null;
        $defender = null;
        $defenderId = null;
        $enemy = null;
        $attacker = null;
        $battleResult = null;
        $victory = false;
        $deletedIds = null;
        $castleId = null;
        $rollbackPath = null;

//        $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);

        $army = $user->parameters['game']->getPlayerArmy($user->parameters['playerId'], $attackerArmyId);

        if (empty($army)) {
            $gameHandler->sendError($user, 'Brak armii o podanym ID! Odświerz przeglądarkę.');
            return;
        }

//        $fields = Cli_Model_Army::getEnemyArmiesFieldsPositions($user->parameters['gameId'], $db, $user->parameters['playerId']);
        $fields = $user->parameters['game']->getFields();

        if ($fields->getType($army->getX(), $army->getY()) == 'w') {
            if ($army->canSwim() || $army->canFly()) {
                $otherArmyId = $user->parameters['game']->isOtherArmyAtPosition($user->parameters['playerId'], $attackerArmyId);
                if ($otherArmyId) {
                    $otherArmy = $user->parameters['game']->getPlayerArmy($user->parameters['playerId'], $otherArmyId);
                    if (!$otherArmy->canSwim() && !$otherArmy->canFly()) {
                        new Cli_Model_JoinArmy($otherArmyId, $user, $db, $gameHandler);
                        $gameHandler->sendError($user, 'Nie możesz zostawić armii na wodzie.');
                        return;
                    }
                }
            }
        } elseif ($fields->getType($army->getX(), $army->getY()) == 'M') {
            $otherArmyId = $user->parameters['game']->isOtherArmyAtPosition($user->parameters['playerId'], $attackerArmyId);
            if ($otherArmyId) {
                $otherArmy = $user->parameters['game']->getPlayerArmy($user->parameters['playerId'], $otherArmyId);
                if (!$otherArmy->canFly()) {
                    new Cli_Model_JoinArmy($otherArmyId, $user, $db, $gameHandler);
                    $gameHandler->sendError($user, 'Nie możesz zostawić armii w górach.');
                    return;
                }
            }
        }

//        $castlesSchema = Zend_Registry::get('castles');
//        $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);
//        $allCastles = $mCastlesInGame->getAllCastles();
//        $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
//        $teamPlayerIds = $mPlayersInGame->getTeamPlayerIds($user->parameters['playerId']);
//        $myCastles = array();
//        foreach ($allCastles as $castle) {
//            if ($castle['playerId'] == $user->parameters['playerId']) {
//                $castle['position'] = $castlesSchema[$castle['castleId']]['position'];
//                $myCastles[] = $castle;
//            }
//        }
//
//
//        $aP = array(
//            'x' => $x,
//            'y' => $y
//        );
//
//        foreach ($castlesSchema as $cId => $castle) {
//            if (!isset($allCastles[$cId])) { // castle is neutral
//                if (Application_Model_Board::isCastleField($aP, $castle['position'])) { // trakuję neutralny zamek jak własny ponieważ go atakuję i jeśli wygram to będę mógł po nim chodzić
//                    $fields = Application_Model_Board::changeCastleFields($fields, $castle['position']['x'], $castle['position']['y'], 'E');
//                    $castleId = $cId;
//                    $defenderColor = 'neutral';
//                } else {
//                    $fields = Application_Model_Board::changeCastleFields($fields, $castle['position']['x'], $castle['position']['y'], 'e');
//                }
//                continue;
//            }
//
//            if ($allCastles[$cId]['razed']) { // castle is razed
//                continue;
//            }
//
//            if ($user->parameters['playerId'] == $allCastles[$cId]['playerId']) { // my castle
//                $fields = Application_Model_Board::changeCastleFields($fields, $castle['position']['x'], $castle['position']['y'], 'c');
//            } elseif (isset($teamPlayerIds[$allCastles[$cId]['playerId']])) { // team castle
//                $fields = Application_Model_Board::changeCastleFields($fields, $castle['position']['x'], $castle['position']['y'], 'c');
//            } else { // enemy castle
//                if (Application_Model_Board::isCastleField($aP, $castle['position'])) { // trakuję zamek wroga jak własny ponieważ go atakuję i jeśli wygram to będę mógł po nim chodzić
//                    $fields = Application_Model_Board::changeCastleFields($fields, $castle['position']['x'], $castle['position']['y'], 'E');
//                    $castleId = $cId;
//                } else {
//                    $fields = Application_Model_Board::changeCastleFields($fields, $castle['position']['x'], $castle['position']['y'], 'e');
//                }
//            }
//        }

//        $field = $fields->getField($x, $y);
//        switch ($field->getType()) {
//            case '':
//                $fight = true;
//                $fields = Application_Model_Board::changeCastleFields($fields, $castle['position']['x'], $castle['position']['y'], 'E');
//                break;
//            case 'nc':
//                $fight = true;
//                $fields = Application_Model_Board::changeCastleFields($fields, $castle['position']['x'], $castle['position']['y'], 'E');
//                break;
//            case 'ea':
//                $fight = true;
//                $fields = Application_Model_Board::changeArmyField($fields, $x, $y, 'E');
//                break;
//            case 'su':
//                $fields = Application_Model_Board::changeArmyField($fields, $x, $y, 'b');
//                break;
//
//        }
//
//        if ($castleId === null) {
//            $defenderId = $army->getEnemyPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db);
//            if ($defenderId) { // enemy army
//                $fields = Application_Model_Board::changeArmyField($fields, $x, $y, 'E');
//            } else { // idziemy nie walczymy
//                if ($mArmy2->areMySwimmingUnitsAtPosition(array('x' => $x, 'y' => $y), $user->parameters['playerId'])) {
//                    $fields = Application_Model_Board::changeArmyField($fields, $x, $y, 'b');
//                }
//            }
//        }

        /*
         * A* START
         */


        try {
            $A_Star = new Cli_Model_Astar($army, $x, $y, $fields);
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

            $gameHandler->sendToUser($user, $db, $token, $user->parameters['gameId']);
            return;
        }

//        if ($move['movesSpend'] > $army['movesLeft']) {
//            $msg = 'Próba wykonania większej ilości ruchów niż jednostka posiada';
//            echo($msg);
//            $gameHandler->sendError($user, $msg);
//            return;
//        }

        $fight = false;

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        if (Zend_Validate::is($castleId, 'Digits') && Application_Model_Board::isCastleField($move->end, $castlesSchema[$castleId]['position'])) { // enemy castle
//        if (Zend_Validate::is($castleId, 'Digits')) { // enemy castle (ZAMIENIĆ?)
            $fight = true;
            if ($defenderColor == 'neutral') {
                $enemy = new Cli_Model_Army(Cli_Model_Battle::getNeutralCastleGarrison($user->parameters['gameId'], $db));
                $defenderId = 0;
            } else { // kolor wrogiego zamku sprawdzam dopiero wtedy gdy wiem, że armia ma na niego zasięg
                $defenderId = $mCastlesInGame->getPlayerIdByCastleId($castleId);
                $defenderColor = $playersInGameColors[$defenderId];
                $enemy = new Cli_Model_Army(Cli_Model_Army::getCastleGarrisonFromCastlePosition($castlesSchema[$castleId]['position'], $user->parameters['gameId'], $db));
                $enemy->addCastleDefenseModifier($castleId, $user->parameters['gameId'], $db);
                $enemy->setCombatDefenseModifiers();
            }
        } elseif ($defenderId && $move->x == $x && $move->y == $y) { // enemy army
            $fight = true;
            $defenderColor = $playersInGameColors[$defenderId];
            $enemy = new Cli_Model_Army($mArmy2->getAllEnemyUnitsFromPosition($move->end, $user->parameters['playerId']));
            $enemy->setCombatDefenseModifiers();
            $enemy->addTowerDefenseModifier();
        }

        /* ------------------------------------
         *
         * ZMIANY ZAPISUJĘ PONIZEJ TEJ LINII
         *
         * ------------------------------------ */

        if ($fight) {
            $battle = new Cli_Model_Battle($army, $enemy, Cli_Model_Army::getAttackSequence($user->parameters['gameId'], $db, $user->parameters['playerId']), Cli_Model_Army::getDefenceSequence($user->parameters['gameId'], $db, $defenderId));
            $battle->fight();
            $battle->updateArmies($user->parameters['gameId'], $db, $user->parameters['playerId'], $defenderId);

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
                        $mCastlesInGame->addCastle($castleId, $user->parameters['playerId']);
                    } else {
                        $mCastlesInGame->changeOwner($castlesSchema[$castleId], $user->parameters['playerId']);
                    }
                }
                $army->updateArmyPosition($user->parameters['playerId'], $move, $fields, $user->parameters['gameId'], $db);
                $attacker = Cli_Model_Army::getArmyByArmyIdPlayerId($attackerArmyId, $user->parameters['playerId'], $user->parameters['gameId'], $db);
                $victory = true;
//                foreach ($enemy['ids'] as $id) {
//                    $defender[]['armyId'] = $id;
//                }
            } else {
                $mArmy2->destroyArmy($army->id, $user->parameters['playerId']);
                $attacker = array(
                    'armyId' => $attackerArmyId,
                    'destroyed' => true
                );
                if ($defenderColor == 'neutral') {
                    $defender = null;
                }
            }
            $battleResult = $battle->getResult();
        } else {
            $army->updateArmyPosition($user->parameters['playerId'], $move, $fields, $user->parameters['gameId'], $db);
            $deletedIds = $user->parameters['game']->joinArmiesAtPosition($user->parameters['playerId'], $army->getId(), $db);
        }

        new Cli_Model_TowerHandler($move->current, $user, $db, $gameHandler);

        $token = array(
            'type' => 'move',
            'attackerColor' => $playersInGameColors[$user->parameters['playerId']],
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

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}