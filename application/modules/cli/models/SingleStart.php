<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_SingleStart
{
    /**
     * Cli_Model_SingleStart constructor.
     * @param WebSocketTransportInterface $user
     * @param Cli_MainHandler $handler
     * @param $gameId
     * @throws Exception
     */
    public function __construct(WebSocketTransportInterface $user, Cli_MainHandler $handler, $gameId)
    {
        $db = $handler->getDb();
        $mPlayer = new Application_Model_Player($db);
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $mGame = new Application_Model_Game($gameId, $db);

        $mapId = $mGame->getMapId();

        $mMap = new Application_Model_Map($mapId, $db);
        $mMapCastles = new Application_Model_MapCastles($mapId, $db);
        $mMapPlayers = new Application_Model_MapPlayers($mapId, $db);

        $teamMaxPlayers = $mMap->getMaxPlayers() / 2;
        $startPositions = $mMapCastles->getDefaultStartPositions();
        $first = true;
        $i = 0;

        foreach ($mMapPlayers->getAll() as $mapPlayerId => $mapPlayer) {
            $i++;
            if ($teamMaxPlayers > $i) {
                $teamId = 1;
            } else {
                $teamId = 2;
            }
            if ($first) {
                $playerId = $user->parameters['playerId'];
                $mTurn = new Application_Model_TurnHistory($gameId, $db);
                $mTurn->add($playerId, 1);
                $mGame->startGame($playerId);
                $first = false;
            } else {
                $playerId = $mPlayer->getComputerPlayerId($mPlayersInGame->getOtherComputerPlayerIdSelect());
                if (empty($playerId)) {
                    throw new Exception('kamieni kupa!');
                }
            }

            $mPlayersInGame->joinGame($playerId, $mapPlayerId, $teamId);

            $mHero = new Application_Model_Hero($playerId, $db);
            $mArmy = new Application_Model_Army($gameId, $db);

            $armyId = $mArmy->createArmy($startPositions[$mapPlayer['mapPlayerId']], $playerId);

            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
            $mHeroesInGame->add($armyId, $mHero->getFirstHeroId());

            $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
            $mCastlesInGame->addCastle($startPositions[$mapPlayer['mapPlayerId']]['mapCastleId'], $playerId);
        }


        $token = array(
            'type' => 'game',
            'action' => 'index',
            'gameId' => $gameId
        );

        $handler->sendToUser($user, $token);
    }
}