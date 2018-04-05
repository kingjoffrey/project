<?php

class Cli_Model_RuinHandler
{
    public function __construct($x, $y, Cli_Model_Heroes $heroes, Cli_Model_Game $game, WebSocketTransportInterface $user, $handler)
    {
        if (!$heroes->exists()) {
            return;
        }

        $gameId = $game->getId();
        $fields = $game->getFields();
        $db = $handler->getDb();

        $bonus = false;

        if ($fields->hasField($x, $y) && $mapRuinId = $fields->getField($x, $y)->getRuinId()) {
            $mapRuin = $game->getRuins()->getRuin($mapRuinId);
            $array = array('mapRuinId' => $mapRuin->getId(), 'ruinId' => $mapRuin->getType());

            foreach ($heroes->getKeys() as $heroId) {
                $hero = $heroes->getHero($heroId);
                if (!$hero->hasMapRuinBonus($mapRuinId)) {
                    $bonus = true;
                    $hero->addMapRuinBonus($array, $gameId, $db);
                }
            }

            if ($bonus) {
                $token = array(
                    'type' => 'ruin',
                    'bonus' => $array['ruinId']
                );
                $handler->sendToUser($user, $token);
            }

        }
    }
}