<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_SetupStart
{
    /**
     * Cli_Model_SetupStart constructor.
     * @param $dataIn
     * @param WebSocketTransportInterface $user
     * @param Cli_NewHandler $handler
     */
    public function __construct($dataIn, WebSocketTransportInterface $user, Cli_NewHandler $handler)
    {
        $setup = SetupGame::getSetup($user);
        if (!$setup->isGameMaster($user->parameters['playerId'])) {
            echo('Not game master!');
            return;
        }

        $players = $setup->getPlayers();
        $inGame = false;
        foreach ($players as $player) {
            if ($player['mapPlayerId']) {
                $inGame = true;
            }
        }
        if (empty($inGame)) {
            echo('Not players in game!');
            return;
        }

        $setup->setIsOpen(false);
        $db = $handler->getDb();
        $mPlayersInGame = new Application_Model_PlayersInGame($setup->getGameId(), $db);

        $mGame = new Application_Model_Game($setup->getGameId(), $db);
        $mapId = $mGame->getMapId();

        $mMapCastles = new Application_Model_MapCastles($mapId, $db);
        $startPositions = $mMapCastles->getDefaultStartPositions();

        $mMapPlayers = new Application_Model_MapPlayers($mapId, $db);

        $first = true;

        foreach ($mMapPlayers->getAll() as $mapPlayerId => $mapPlayer) {
            $playerId = $setup->getPlayerIdByMapPlayerId($mapPlayerId);
            if (empty($playerId)) {
                echo('brak wszystkich graczy' . "\n");
                return;
            }
            if ($first) {
                $mTurn = new Application_Model_TurnHistory($setup->getGameId(), $db);
                $mTurn->add($playerId, 1);
                $mGame->startGame($playerId);
                $first = false;
            }

            $mPlayersInGame->joinGame($playerId, $mapPlayerId);
            $mPlayersInGame->setTeam($playerId, $mapPlayerId);

            $mHero = new Application_Model_Hero($playerId, $db);
            $mArmy = new Application_Model_Army($setup->getGameId(), $db);

            $armyId = $mArmy->createArmy($startPositions[$mapPlayer['mapPlayerId']], $playerId);

            $mHeroesInGame = new Application_Model_HeroesInGame($setup->getGameId(), $db);
            $mHeroesInGame->add($armyId, $mHero->getFirstHeroId());

            $mCastlesInGame = new Application_Model_CastlesInGame($setup->getGameId(), $db);
            $mCastlesInGame->addCastle($startPositions[$mapPlayer['mapPlayerId']]['mapCastleId'], $playerId);
        }

        $token = array('type' => 'start');
        $handler->sendToChannel($setup, $token);
    }
}