<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_ComputerHeroResurrection
{
    private $_ressurrected = 0;

    public function __construct($playerId, WebSocketTransportInterface $user, $handler)
    {
        $game = Cli_CommonHandler::getGameFromUser($user);
        $gameId = $game->getId();
        $players = $game->getPlayers();
        $color = $game->getPlayerColor($playerId);
        $player = $players->getPlayer($color);

        if ($player->getGold() < 100) {
            return;
        }

        if (!$capital = $player->getCastles()->getCastle($player->getCapitalId())) {
            return;
        }

        $db = $handler->getDb();
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $hero = $mHeroesInGame->getDeadHero($playerId);

        if (empty($hero)) {
            return;
        }

        if (!$armyId = $player->getArmies()->getArmyIdFromField($game->getFields()->getField($capital->getX(), $capital->getY()))) {
            $armyId = $player->getArmies()->create($capital->getX(), $capital->getY(), $color, $game, $db);
        }

        $mHeroSkills = new Application_Model_Heroskills($db);
        $mHeroesToMapRuins = new Application_Model_HeroesToMapRuins($gameId, $db);

        $army = $player->getArmies()->getArmy($armyId);
        $army->addHero($hero['heroId'],
            new Cli_Model_Hero(
                $hero,
                $mHeroSkills->getBonuses($hero['heroId']),
                $mHeroesToMapRuins->getHeroMapRuins($hero['heroId'])
            ), $gameId, $db);
        $army->getHeroes()->getHero($hero['heroId'])->zeroMovesLeft($gameId, $db);

        $l = new Coret_Model_Logger();
        $l->log('WSKRZESZAM HEROSA id = ' . $hero['heroId']);


        $player->addGold(-100);
        $player->saveGold($gameId, $db);

        $token = array(
            'type' => 'resurrection',
            'army' => $army->toArray(),
            'color' => $color
        );

        $handler->sendToChannel($token);

        $this->_ressurrected = 1;
    }

    public function justRessurected()
    {
        return $this->_ressurrected;
    }
}