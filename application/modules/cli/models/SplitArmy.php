<?php

class Cli_Model_SplitArmy
{
    private $_childArmyId = null;

    function  __construct($parentArmyId, $s, $h, $playerId, IWebSocketConnection $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if (empty($parentArmyId) || (empty($h) && empty($s))) {
            $gameHandler->sendError($user, 'Brak "armyId", "s" lub "h"!');
            return;
        }

        $heroesIds = explode(',', $h);
        $soldiersIds = explode(',', $s);
        $game = $this->getGame($user);
        $gameId = $game->getId();
        $color = $game->getPlayerColor($playerId);
        $armies = $game->getPlayers()->getPlayer($color)->getArmies();
        $army = $armies->getArmy($parentArmyId);

        if (isset($heroesIds[0]) && $heroesIds[0]) {

            foreach ($heroesIds as $heroId) {
                if (!Zend_Validate::is($heroId, 'Digits')) {
                    continue;
                }
                if (!$army->getHeroes()->hasHero($heroId)) {
                    continue;
                }

                if (empty($this->_childArmyId)) {
                    $this->_childArmyId = $armies->create($army->getX(), $army->getY(), $color, $game, $db);
                    $childArmy = $armies->getArmy($this->_childArmyId);
                }
                $armies->changeHeroAffiliation($parentArmyId, $this->_childArmyId, $heroId, $gameId, $db);
            }
        }

        if (isset($soldiersIds) && $soldiersIds) {
            foreach ($soldiersIds as $soldierId) {
                if (!Zend_Validate::is($soldierId, 'Digits')) {
                    continue;
                }

                if ($army->getWalkingSoldiers()->hasSoldier($soldierId)) {
                    if (empty($this->_childArmyId)) {
                        $this->_childArmyId = $armies->create($army->getX(), $army->getY(), $color, $game, $db);
                        $childArmy = $armies->getArmy($this->_childArmyId);
                    }
                    $armies->changeWalkingSoldierAffiliation($parentArmyId, $this->_childArmyId, $soldierId, $gameId, $db);
                } elseif ($army->getSwimmingSoldiers()->hasSoldier($soldierId)) {
                    if (empty($this->_childArmyId)) {
                        $this->_childArmyId = $armies->create($army->getX(), $army->getY(), $color, $game, $db);
                        $childArmy = $armies->getArmy($this->_childArmyId);
                    }
                    $armies->changeSwimmingSoldierAffiliation($parentArmyId, $this->_childArmyId, $soldierId, $gameId, $db);
                } elseif ($army->getFlyingSoldiers()->hasSoldier($soldierId)) {
                    if (empty($this->_childArmyId)) {
                        $this->_childArmyId = $armies->create($army->getX(), $army->getY(), $color, $game, $db);
                        $childArmy = $armies->getArmy($this->_childArmyId);
                    }
                    $armies->changeFlyingSoldierAffiliation($parentArmyId, $this->_childArmyId, $soldierId, $gameId, $db);
                }
            }
        }

        if (empty($this->_childArmyId)) {
            $gameHandler->sendError($user, 'Brak "childArmyId"');
            return;
        }

        $token = array(
            'type' => 'split',
            'parentArmy' => $army->toArray(),
            'childArmy' => $childArmy->toArray(),
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $gameId);
    }

    public function getChildArmyId()
    {
        return $this->_childArmyId;
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