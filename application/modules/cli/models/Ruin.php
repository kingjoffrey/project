<?php

class Cli_Model_Ruin extends Cli_Model_Entity
{
    private $_empty;
    private $_type;

    public function __construct($ruin, $empty)
    {
        $this->_id = $ruin['ruinId'];
        $this->_x = $ruin['x'];
        $this->_y = $ruin['y'];
        $this->_empty = $empty;
        $this->_type = $ruin['type'];
    }

    public function toArray()
    {
        return array(
            'empty' => $this->_empty,
            'x' => $this->_x,
            'y' => $this->_y,
            'type' => $this->_type
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

    public function search(Cli_Model_Game $game, Cli_Model_Army $army, $heroId, $playerId, $handler)
    {
        $random = rand(0, 100);
        $gameId = $game->getId();
        $color = $game->getPlayerColor($playerId);
        $db = $handler->getDb();

        if ($random < 10) { //10%
//śmierć
            $turnNumber = $game->getTurnNumber();
            if ($turnNumber <= 7) {
                $army->getHeroes()->getHero($heroId)->zeroMovesLeft($gameId, $db);
                $found = array('null', 1);
            } else {
                $found = array('death', 1);
                $army->removeHero($heroId, $playerId, 0, $gameId, $db);
                if (!$army->count()) {
                    $game->getPlayers()->getPlayer($game->getPlayerColor($playerId))->getArmies()->removeArmy($army->getId(), $game, $db);
                }
            }
        } elseif ($random < 55) { //45%
//kasa
            $gold = rand(50, 150);
            $found = array('gold', $gold);
            $player = $game->getPlayers()->getPlayer($color);
            $player->addGold($gold);
            $player->saveGold($gameId, $db);
            $army->getHeroes()->getHero($heroId)->zeroMovesLeft($gameId, $db);
            $this->setEmpty($gameId, $db);
        } elseif ($random < 90) { //40%
//jednostki
            $turnNumber = $game->getTurnNumber();
            $maxSpecialUnitKey = $game->getUnits()->countSpecialUnits() - 1;

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
                $max1 = $maxSpecialUnitKey - 1;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turnNumber <= 21) {
                $min1 = 0;
                $max1 = $maxSpecialUnitKey;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turnNumber <= 25) {
                $min1 = 0;
                $max1 = $maxSpecialUnitKey;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turnNumber <= 32) {
                $min1 = 0;
                $max1 = $maxSpecialUnitKey;
                $min2 = 1;
                $max2 = 2;
            } elseif ($turnNumber <= 39) {
                $min1 = 0;
                $max1 = $maxSpecialUnitKey;
                $min2 = 1;
                $max2 = 3;
            } elseif ($turnNumber <= 46) {
                $min1 = 0;
                $max1 = $maxSpecialUnitKey;
                $min2 = 2;
                $max2 = 3;
            } elseif ($turnNumber <= 53) {
                $min1 = 0;
                $max1 = $maxSpecialUnitKey;
                $min2 = 3;
                $max2 = 3;
            } elseif ($turnNumber <= 60) {
                $min1 = 0;
                $max1 = $maxSpecialUnitKey;
                $min2 = 4;
                $max2 = 4;
            } elseif ($turnNumber <= 67) {
                $min1 = 0;
                $max1 = $maxSpecialUnitKey;
                $min2 = 5;
                $max2 = 5;
            } elseif ($turnNumber <= 74) {
                $min1 = 0;
                $max1 = $maxSpecialUnitKey;
                $min2 = 6;
                $max2 = 6;
            } elseif ($turnNumber <= 81) {
                $min1 = 0;
                $max1 = $maxSpecialUnitKey;
                $min2 = 7;
                $max2 = 7;
            } elseif ($turnNumber <= 88) {
                $min1 = 0;
                $max1 = $maxSpecialUnitKey;
                $min2 = 8;
                $max2 = 8;
            } elseif ($turnNumber <= 95) {
                $min1 = 0;
                $max1 = $maxSpecialUnitKey;
                $min2 = 9;
                $max2 = 9;
            } elseif ($turnNumber <= 102) {
                $min1 = 0;
                $max1 = $maxSpecialUnitKey;
                $min2 = 10;
                $max2 = 10;
            } else {
                $min1 = 0;
                $max1 = $maxSpecialUnitKey;
                $min2 = 11;
                $max2 = 11;
            }

            $numberOfUnits = rand($min2, $max2);

            for ($i = 0; $i < $numberOfUnits; $i++) {
                $army->createSoldier($gameId, $playerId, $game->getUnits()->getSpecialUnitId(rand($min1, $max1)), $db);
            }

            $army->getHeroes()->getHero($heroId)->zeroMovesLeft($gameId, $db);
            $this->setEmpty($gameId, $db);
            $found = array('allies', $numberOfUnits);
        } else { // 10%
//nic
            $army->getHeroes()->getHero($heroId)->zeroMovesLeft($gameId, $db);
            $found = array('null', 1);
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

        $handler->sendToChannel($token);
    }

    public function giveMeDragons(Cli_Model_Game $game, Cli_Model_Army $army, $heroId, $playerId, $handler)
    {
        $numberOfUnits = 3;
        $gameId = $game->getId();
        $color = $game->getPlayerColor($playerId);
        $db = $handler->getDb();

        $dragonId = $game->getUnits()->getDragonId();

        for ($i = 0; $i < $numberOfUnits; $i++) {
            $army->createSoldier($gameId, $playerId, $dragonId, $db);
        }

        $army->getHeroes()->getHero($heroId)->zeroMovesLeft($gameId, $db);
        $this->setEmpty($gameId, $db);
        $found = array('allies', $numberOfUnits);

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

        $handler->sendToChannel($token);
    }
}
