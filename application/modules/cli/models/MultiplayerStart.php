<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_MultiplayerStart
{
    /**
     * Cli_Model_SetupStart constructor.
     * @param $dataIn
     * @param WebSocketTransportInterface $user
     * @param Cli_OpenGamesHandler $handler
     */
    public function __construct($dataIn, WebSocketTransportInterface $user, Cli_OpenGamesHandler $handler)
    {
        $openGame = OpenGame::getGame($user);
        if (!$openGame->isGameMaster($user->parameters['playerId'])) {
            echo('Not game master!');
            return;
        }

        $players = $openGame->getPlayers();
        $inGame = false;
        foreach ($players as $player) {
            if ($player['sideId']) {
                $inGame = true;
            }
        }
        if (empty($inGame)) {
            echo('Not players in game!');
            return;
        }

        $openGame->setIsOpen(false);
        $db = $handler->getDb();
        $mPlayersInGame = new Application_Model_PlayersInGame($openGame->getGameId(), $db);
        $mGame = new Application_Model_Game($openGame->getGameId(), $db);

        $mapId = $mGame->getMapId();

        $mMap = new Application_Model_Map($mapId, $db);
        $mMapCastles = new Application_Model_MapCastles($mapId, $db);

        $teamMaxPlayers = $mMap->getMaxPlayers() / 2;
        $startPositions = $mMapCastles->getDefaultStartPositions();
        $first = true;
        $i = 0;

        foreach (array_keys($startPositions) as $sideId) {
            $playerId = $openGame->getPlayerIdBySideId($sideId);
            if (empty($playerId)) {
                echo('brak wszystkich graczy' . "\n");
                return;
            }
            if ($first) {
                $mTurn = new Application_Model_TurnHistory($openGame->getGameId(), $db);
                $mTurn->add($playerId, 1);
                $mGame->startGame($playerId);
                $first = false;
            }

            $i++;
            if ($teamMaxPlayers >= $i) {
                $teamId = 1;
            } else {
                $teamId = 2;
            }
            $mPlayersInGame->joinGame($playerId, $sideId, $teamId);

            $mHero = new Application_Model_Hero($playerId, $db);
            $mArmy = new Application_Model_Army($openGame->getGameId(), $db);

            $armyId = $mArmy->createArmy($startPositions[$sideId], $playerId);

            $mHeroesInGame = new Application_Model_HeroesInGame($openGame->getGameId(), $db);
            $mHeroesInGame->add($armyId, $mHero->getFirstHeroId());

            $mCastlesInGame = new Application_Model_CastlesInGame($openGame->getGameId(), $db);
            $mCastlesInGame->addCastle($startPositions[$sideId]['mapCastleId'], $playerId);
        }

        $token = array('type' => 'start');
        $handler->sendToChannel($openGame, $token);
    }
}