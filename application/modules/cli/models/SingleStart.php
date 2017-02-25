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

        $maxPlayers = $mMap->getMaxPlayers();
        $teamMaxPlayers = $maxPlayers / 2;
        $startPositions = $mMapCastles->getDefaultStartPositions();
        $first = true;
        $i = 0;

        foreach (array_keys($startPositions) as $sideId) {
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

            $i++;
            if ($teamMaxPlayers >= $i) {
                $teamId = 1;
            } else {
                $teamId = 2;
            }
            $mPlayersInGame->joinGame($playerId, $sideId, $teamId);

            $mHero = new Application_Model_Hero($playerId, $db);
            $mArmy = new Application_Model_Army($gameId, $db);

            $armyId = $mArmy->createArmy($startPositions[$sideId], $playerId);

            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
            $mHeroesInGame->add($armyId, $mHero->getFirstHeroId());

            $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
            $mCastlesInGame->addCastle($startPositions[$sideId]['mapCastleId'], $playerId);
        }


        $token = array(
            'type' => 'game',
            'action' => 'index',
            'gameId' => $gameId
        );

        $handler->sendToUser($user, $token);
    }
}