<?php

class Cli_Model_Open
{
    /**
     * @param $dataIn
     * @param IWebSocketConnection $user
     * @param Zend_Db_Adapter_Pdo_Pgsql $db
     * @param Cli_GameHandler $gameHandler
     * @throws Exception
     */
    public function __construct($dataIn, IWebSocketConnection $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if (!isset($dataIn['gameId']) || !isset($dataIn['playerId']) || !isset($dataIn['langId'])) {
            $gameHandler->sendError($user, 'Brak "gameId" lub "playerId" lub "langId');
            return;
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($dataIn['gameId'], $db);

        if (!$mPlayersInGame->checkAccessKey($dataIn['playerId'], $dataIn['accessKey'], $db)) {
            $gameHandler->sendError($user, 'Brak uprawnień!');
            return;
        }

        $mPlayersInGame->updateWSSUId($dataIn['playerId'], $user->getId());

        Zend_Registry::set('id_lang', $dataIn['langId']);
        Zend_Registry::set('playersInGameColors', $mPlayersInGame->getAllColors());

        if (!($user->parameters['game'] = $gameHandler->getGame($dataIn['gameId']))) {
            echo 'not set' . "\n";
            $gameHandler->addGame($dataIn['gameId'], new Cli_Model_Game($dataIn['gameId'], $db));
            $user->parameters['game'] = $gameHandler->getGame($dataIn['gameId']);
        }

        $game = $this->getGame($user);
        $myColor = $game->getPlayerColor($dataIn['playerId']);
        $user->parameters['me'] = new Cli_Model_Me($myColor, $dataIn['playerId']);

//        $fields = $game->getFields();
//
//        foreach ($game->getPlayers()->getKeys() as $color) {
//            $player = $game->getPlayers()->getPlayer($color);
//            if (!$game->getPlayers()->sameTeam($myColor, $color)) {
//                $player->initFieldsTemporaryType($fields);
//            } elseif ($color == $myColor) {
//                $castles = $player->getCastles();
//                foreach ($castles->getKeys() as $castleId) {
//                    $castle = $castles->getCastle($castleId);
//                    for ($x = $castle->getX(); $x <= $castle->getX() + 1; $x++) {
//                        for ($y = $castle->getY(); $y <= $castle->getY(); $y++) {
//                            $fields->getField($x, $y)->setTemporaryType('c');
//                        }
//                    }
//                }
//            }
//        }

        $token = $game->toArray();
        $token['color'] = $myColor;
        $token['gold'] = $game->getPlayers()->getPlayer($myColor)->getGold();
        $token['type'] = 'open';

        $gameHandler->sendToUser($user, $db, $token, $dataIn['gameId']);
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