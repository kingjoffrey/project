<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;


class Cli_Model_RuinHandler
{
    private $_bonus = 0;

    public function __construct($x, $y, Cli_Model_Heroes $heroes, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        if (!$heroes->exists()) {
            return;
        }

        $gameId = $game->getId();
        $fields = $game->getFields();


        if ($fields->hasField($x, $y) && $mapRuinId = $fields->getField($x, $y)->getRuinId()) {
            $mapRuin = $game->getRuins()->getRuin($mapRuinId);
            $array = array('mapRuinId' => $mapRuin->getId(), 'ruinId' => $mapRuin->getType());

            foreach ($heroes->getKeys() as $heroId) {
                $hero = $heroes->getHero($heroId);
                if (!$hero->hasMapRuinBonus($mapRuinId)) {
                    $this->_bonus = $array['ruinId'];
                    $hero->addMapRuinBonus($array, $gameId, $db);
                }
            }


        }
    }

    public function hasBonus()
    {
        return $this->_bonus;
    }

    public function sendBonus(WebSocketTransportInterface $user, $handler)
    {
        $token = array(
            'type' => 'ruin',
            'bonus' => $this->_bonus
        );
        $handler->sendToUser($user, $token);
    }
}