<?php

class Cli_Model_Open
{
    private $_me;

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

        Zend_Registry::set('id_lang', $dataIn['langId']);
        Zend_Registry::set('playersInGameColors', $mPlayersInGame->getAllColors());

        if (!isset($user->parameters['game'])) {
            echo 'not set' . "\n";
            $user->parameters['game'] = new Cli_Model_Game($dataIn['gameId'], $db);
        }
        $this->_me = new Cli_Model_Me($user->parameters['game']->getPlayerColor($dataIn['playerId']), $dataIn['playerId']);
        $myColor = $this->_me->getColor();
        foreach ($user->parameters['game']->getPlayers()->getKeys() as $color) {
            if (!$user->parameters['game']->getPlayers()->sameTeam($myColor, $color)) {
                $user->parameters['game']->getPlayers()->getPlayer($color)->initFieldsTemporaryType($user->parameters['game']->getFields());
            }
        }

        $token = $user->parameters['game']->toArray();
        $token['me'] = $this->_me->toArray();
        $token['gold'] = $user->parameters['game']->getPlayers()->getPlayer($myColor)->getGold();
        $token['type'] = 'open';

        $gameHandler->sendToUser($user, $db, $token, $dataIn['gameId']);
    }

    /**
     * @return Cli_Model_Me
     */
    public function getMe()
    {
        return $this->_me;
    }
}