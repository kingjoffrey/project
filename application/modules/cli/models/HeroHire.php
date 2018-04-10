<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_HeroHire
{
    private $_price = 1000;

    public function __construct($playerId, WebSocketTransportInterface $user, $handler)
    {
        $game = Cli_CommonHandler::getGameFromUser($user);
        $gameId = $game->getId();
        $color = $game->getPlayerColor($playerId);
        $player = $game->getPlayers()->getPlayer($color);

        if ($player->getGold() < $this->_price) {
            $l = new Coret_Model_Logger('Cli_Model_HeroHire');
            $l->log('Za mało złota!');
            if (!$player->getComputer()) {
                $handler->sendError($user, 'Error 1008');
            }
            return;
        }

        if (!$capital = $player->getCastles()->getCastle($player->getCapitalId())) {
            $l = new Coret_Model_Logger('Cli_Model_HeroHire');
            $l->log('Aby wynająć herosa musisz posiadać stolicę!');
            if (!$player->getComputer()) {
                $handler->sendError($user, 'Error 1009');
            }
            return;
        }

        $db = $handler->getDb();
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroSkills = new Application_Model_Heroskills($db);
        $mHeroesToMapRuins = new Application_Model_HeroesToMapRuins($gameId, $db);

        $heroId = $player->getAllHeroes()->hire($player->getArmies(), $db);

        // jeśli nie ma armii na spawnie w stolicy to stwórz ją
        if (!$armyId = $player->getArmies()->getArmyIdFromField($game->getFields()->getField($capital->getX(), $capital->getY()))) {
            $armyId = $player->getArmies()->create($capital->getX(), $capital->getY(), $color, $game, $db);
        }

        if(!$hero = $mHeroesInGame->getHero($heroId)){
            $mHeroesInGame->add($armyId, $heroId);
            $hero = $mHeroesInGame->getHero($heroId);
        }

        $army = $player->getArmies()->getArmy($armyId);

        $army->addHero($heroId, new Cli_Model_Hero(
            $hero,
            $mHeroSkills->getBonuses($hero['heroId']),
            $mHeroesToMapRuins->getHeroMapRuins($hero['heroId'])
        ), $gameId, $db);

        $army->getHeroes()->getHero($heroId)->zeroMovesLeft($gameId, $db);

        $player->addGold(-$this->_price);
        $player->saveGold($gameId, $db);

        $token = array(
            'type' => 'resurrection',
            'army' => $army->toArray(),
            'gold' => $this->_price,
            'color' => $color
        );

        $handler->sendToChannel($token);
    }
}
