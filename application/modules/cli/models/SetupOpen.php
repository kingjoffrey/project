<?php

class Cli_Model_SetupOpen
{
    /**
     * @param $dataIn
     * @param Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Zend_Db_Adapter_Pdo_Pgsql $db
     * @param Cli_GameHandler $handler
     * @throws Exception
     */
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_SetupHandler $handler)
    {
        if (!isset($dataIn['gameId'])) {
            throw new Exception('Brak "gameId"');
            return;
        }
        if (!isset($dataIn['playerId'])) {
            throw new Exception('Brak "playerId"');
            return;
        }
        if (!isset($dataIn['langId'])) {
            throw new Exception('Brak langId');
            return;
        }

        $db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            throw new Exception('Brak uprawnień!');
            return;
        }

        if (!($user->parameters['game'] = $handler->getGame($dataIn['gameId']))) {
            echo 'not set' . "\n";
            $handler->addGame($dataIn['gameId'], new Cli_Model_Setup($dataIn['gameId'], $db));
            $user->parameters['game'] = $handler->getGame($dataIn['gameId']);
        }

        $setup = Cli_Model_Setup::getSetup($user);
        $setup->addUser($dataIn['playerId'], $user);

        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];

        $setup->update($handler);
    }
}