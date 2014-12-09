<?php

class Cli_Model_Ruin extends Cli_Model_Entity
{
    private $_empty;

    public function __construct($ruin, $empty)
    {
        $this->_id = $ruin['ruinId'];
        $this->_x = $ruin['x'];
        $this->_y = $ruin['y'];
        $this->_empty = $empty;
    }

    public function toArray()
    {
        return array(
            'empty' => $this->_empty,
            'x' => $this->_x,
            'y' => $this->_y,
        );
    }

    public function getEmpty()
    {
        return $this->_empty;
    }

    public function setEmpty($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
        $mRuinsInGame->add($this->_id);
        $this->_empty = true;
    }

    public function search(Cli_Model_Game $game, Cli_Model_Army $army, $heroId, $playerId, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        $random = rand(0, 100);
        $gameId = $game->getId();
        $color = $game->getPlayerColor($playerId);

        if ($random < 10) { //10%
//śmierć
            $turnNumber = $game->getTurnNumber();
            if ($turnNumber <= 7) {
                $army->zeroHeroMovesLeft($heroId, $gameId, $db);
                $found = array('null', 1);
            } else {
                $found = array('death', 1);
                $army->killHero($heroId, $playerId, $gameId, $db);
            }
        } elseif ($random < 55) { //45%
//kasa
            $gold = rand(50, 150);
            $found = array('gold', $gold);
            $player = $game->getPlayers()->getPlayer($color);
            $player->addGold($gold);
            $player->saveGold($gameId, $db);
            $army->zeroHeroMovesLeft($heroId, $gameId, $db);
            $this->setEmpty($gameId, $db);
        } elseif ($random < 85) { //30%
//jednostki
            $turnNumber = $game->getTurnNumber();
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

            $specialUnits = $game->getSpecialUnits();
            $unitId = $specialUnits[rand($min1, $max1)]['unitId'];
            $numberOfUnits = rand($min2, $max2);

            for ($i = 0; $i < $numberOfUnits; $i++) {
                $army->createSoldier($gameId, $playerId, $unitId, $db);
            }

            $army->zeroHeroMovesLeft($heroId, $gameId, $db);
            $this->setEmpty($gameId, $db);
            $found = array('allies', $numberOfUnits);
//        } elseif ($random < 95) { //10%
        } else {
//nic
            $army->zeroHeroMovesLeft($heroId, $gameId, $db);
            $found = array('null', 1);

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

        $token = array(
            'type' => 'ruin',
            'army' => $army->toArray(),
            'ruin' => array(
                'ruinId' => $this->_id,
                'empty' => $this->_empty
            ),
            'find' => $found,
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $gameId);
    }
}
