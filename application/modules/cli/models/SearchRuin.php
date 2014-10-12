<?php

class Cli_Model_SearchRuin
{

    public function __construct($armyId, $user, $db, $gameHandler)
    {
        if (!Zend_Validate::is($armyId, 'Digits')) {
            $gameHandler->sendError($user, 'Brak armii!');
            return;
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($user->parameters['gameId'], $db);
        $hero = $mHeroesInGame->getHeroByArmyIdPlayerId($armyId, $user->parameters['playerId']);

        if (empty($hero)) {
            $gameHandler->sendError($user, 'Tylko Heros może przeszukiwać ruiny!');
            return;
        }

        if ($hero['movesLeft'] == 0) {
            $gameHandler->sendError($user, 'Heros ma za mało ruchów!');
            return;
        }

        $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
        $position = $mArmy2->getArmyPositionByArmyIdPlayerId($armyId, $user->parameters['playerId']);
        $ruinId = Application_Model_Board::confirmRuinPosition($position);

        if (!Zend_Validate::is($ruinId, 'Digits')) {
            $gameHandler->sendError($user, 'Brak ruin');
            return;
        }

        $mRuinsInGame = new Application_Model_RuinsInGame($user->parameters['gameId'], $db);

        if ($mRuinsInGame->ruinExists($ruinId)) {
            $gameHandler->sendError($user, 'Ruiny są już przeszukane.');
            return;
        }

        $found = self::search($user->parameters['gameId'], $ruinId, $hero['heroId'], $armyId, $user->parameters['playerId'], $db);

        if ($mRuinsInGame->ruinExists($ruinId)) {
            $ruin = array(
                'ruinId' => $ruinId,
                'empty' => 1
            );
        } else {
            $ruin = array(
                'ruinId' => $ruinId,
                'empty' => 0
            );
        }

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'ruin',
            'army' => Cli_Model_Army::getArmyByArmyId($armyId, $user->parameters['gameId'], $db),
            'ruin' => $ruin,
            'find' => $found,
            'color' => $playersInGameColors[$user->parameters['playerId']]
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

    static public function search($gameId, $ruinId, $heroId, $armyId, $playerId, $db)
    {
        $mGame = new Application_Model_Game($gameId, $db);
        $turnNumber = $mGame->getTurnNumber();

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        if (!$mHeroesInGame->isThisCorrectHero($playerId, $heroId)) {
            echo('HeroId jest inny');

            return;
        }

        $random = rand(0, 100);

        if ($random < 10) { //10%
//śmierć
            if ($turnNumber <= 7) {
                $mHeroesInGame->zeroHeroMovesLeft($armyId, $heroId, $playerId);
                $found = self::foundNothing();
            } else {
                $found = array('death', 1);
                $mHeroesInGame->armyRemoveHero($heroId);
                $mHeroesKilled = new Application_Model_HeroesKilled($gameId, $db);
                $mHeroesKilled->add($heroId, 0, $playerId);
            }
        } elseif ($random < 55) { //45%
//kasa
            $gold = rand(50, 150);
            $found = array('gold', $gold);

            $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
            $inGameGold = $mPlayersInGame->getPlayerGold($playerId);

            $mPlayersInGame->updatePlayerGold($playerId, $gold + $inGameGold);

            $mHeroesInGame->zeroHeroMovesLeft($armyId, $heroId, $playerId);
            $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
            $mRuinsInGame->add($ruinId);
        } elseif ($random < 85) { //30%
//jednostki
            if ($turnNumber <= 9) {
                $min1 = 1;
                $max1 = 1;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turnNumber <= 13) {
                $min1 = 0;
                $max1 = 1;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turnNumber <= 17) {
                $min1 = 0;
                $max1 = 2;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turnNumber <= 21) {
                $min1 = 0;
                $max1 = 3;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turnNumber <= 25) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turnNumber <= 32) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 1;
                $max2 = 2;
            } elseif ($turnNumber <= 39) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 1;
                $max2 = 3;
            } elseif ($turnNumber <= 46) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 2;
                $max2 = 3;
            } elseif ($turnNumber <= 53) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 3;
                $max2 = 3;
            } elseif ($turnNumber <= 60) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 4;
                $max2 = 4;
            } elseif ($turnNumber <= 67) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 5;
                $max2 = 5;
            } elseif ($turnNumber <= 74) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 6;
                $max2 = 6;
            } elseif ($turnNumber <= 81) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 7;
                $max2 = 7;
            } elseif ($turnNumber <= 88) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 8;
                $max2 = 8;
            } elseif ($turnNumber <= 95) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 9;
                $max2 = 9;
            } elseif ($turnNumber <= 102) {
                $min1 = 0;
                $max1 = 4;
                $min2 = 10;
                $max2 = 10;
            } else {
                $min1 = 0;
                $max1 = 4;
                $min2 = 11;
                $max2 = 11;
            }

            $specialUnits = Zend_Registry::get('specialUnits');

            $unitId = $specialUnits[rand($min1, $max1)]['unitId'];

            $numberOfUnits = rand($min2, $max2);

            $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
            for ($i = 0; $i < $numberOfUnits; $i++) {
                $mSoldier->add($armyId, $unitId);
            }

            $mHeroesInGame->zeroHeroMovesLeft($armyId, $heroId, $playerId);
            $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
            $mRuinsInGame->add($ruinId);

            $found = array('allies', $numberOfUnits);
//        } elseif ($random < 95) { //10%
        } else {
//nic
            $mHeroesInGame->zeroHeroMovesLeft($armyId, $heroId, $playerId);
            $found = self::foundNothing();

//        } else { //5%
////artefakt
//            $artifactId = rand(5, 34);
//
//            $mChest = new Application_Model_Chest($playerId, $db);
//
//            if ($mChest->artifactExists($artifactId)) {
//                $mChest->increaseArtifactQuantity($artifactId);
//            } else {
//                $mChest->add($artifactId);
//            }
//
//            $found = array('artifact', $artifactId);
//
//            Cli_Model_Database::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
//
//            $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
//            $mRuinsInGame->add($ruinId);
//
        }

        return $found;
    }

    static function foundNothing()
    {
//            $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
//            $mRuinsInGame->add($ruinId);
        return array('null', 1);
    }
}