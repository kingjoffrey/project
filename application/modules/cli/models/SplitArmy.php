<?php

class Cli_Model_SplitArmy
{

    function  __construct($parentArmyId, $s, $h, $playerId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_GameHandler $handler)
    {
        if (empty($parentArmyId) || (empty($h) && empty($s))) {
            $handler->sendError($user, 'Brak "armyId", "s" lub "h"!');
            return;
        }

        $heroesIds = explode(',', $h);
        $soldiersIds = explode(',', $s);
        $game = Cli_Model_Game::getGame($user);
        $gameId = $game->getId();
        $color = $game->getPlayerColor($playerId);
        $armies = $game->getPlayers()->getPlayer($color)->getArmies();
        $army = $armies->getArmy($parentArmyId);
        $childArmyId = 0;
        $db = $handler->getDb();

        if (isset($heroesIds[0]) && $heroesIds[0]) {

            foreach ($heroesIds as $heroId) {
                if (!Zend_Validate::is($heroId, 'Digits')) {
                    continue;
                }
                if (!$army->getHeroes()->hasHero($heroId)) {
                    continue;
                }

                if (empty($childArmyId)) {
                    $childArmyId = $armies->create($army->getX(), $army->getY(), $color, $game, $db);
                }
                $armies->changeHeroAffiliation($parentArmyId, $childArmyId, $heroId, $gameId, $db);
            }
        }

        if (isset($soldiersIds) && $soldiersIds) {
            foreach ($soldiersIds as $soldierId) {
                if (!Zend_Validate::is($soldierId, 'Digits')) {
                    continue;
                }

                if ($army->getWalkingSoldiers()->hasSoldier($soldierId)) {
                    if (empty($childArmyId)) {
                        $childArmyId = $armies->create($army->getX(), $army->getY(), $color, $game, $db);
                    }
                    $armies->changeWalkingSoldierAffiliation($parentArmyId, $childArmyId, $soldierId, $gameId, $db);
                } elseif ($army->getSwimmingSoldiers()->hasSoldier($soldierId)) {
                    if (empty($childArmyId)) {
                        $childArmyId = $armies->create($army->getX(), $army->getY(), $color, $game, $db);
                    }
                    $armies->changeSwimmingSoldierAffiliation($parentArmyId, $childArmyId, $soldierId, $gameId, $db);
                } elseif ($army->getFlyingSoldiers()->hasSoldier($soldierId)) {
                    if (empty($childArmyId)) {
                        $childArmyId = $armies->create($army->getX(), $army->getY(), $color, $game, $db);
                    }
                    $armies->changeFlyingSoldierAffiliation($parentArmyId, $childArmyId, $soldierId, $gameId, $db);
                }
            }
        }

        if (empty($childArmyId)) {
            $handler->sendError($user, 'Brak "childArmyId"');
            return;
        }

        $token = array(
            'type' => 'split',
            'parentArmy' => $army->toArray(),
            'childArmy' => $armies->getArmy($childArmyId)->toArray(),
            'color' => $color
        );

        $handler->sendToChannel($game, $token);
    }
}