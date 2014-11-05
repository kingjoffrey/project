<?php

class Cli_Model_Open
{
    public function __construct($dataIn, $user, Zend_Db_Adapter_Pdo_Pgsql $db, $gameHandler)
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

        $game = new Cli_Model_Game($dataIn['playerId'], $dataIn['gameId'], $db);
        $user->parameters = $game;
        $token = $game->toArray();
        $token['type'] = 'open';

        $gameHandler->sendToChannel($db, $token, $dataIn['gameId']);
    }

}