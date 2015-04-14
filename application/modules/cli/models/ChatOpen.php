<?php

class Cli_Model_ChatOpen
{
    /**
     * @param $dataIn
     * @param Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Zend_Db_Adapter_Pdo_Pgsql $db
     * @param Cli_GameHandler $gameHandler
     * @throws Exception
     */
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHandler $gameHandler)
    {
        if (!isset($dataIn['playerId']) || !isset($dataIn['langId'])) {
            throw new Exception('Brak "playerId" lub "langId');
            return;
        }

        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            throw new Exception($user, 'Brak uprawnień!');
            return;
        }

//        Zend_Registry::set('id_lang', $dataIn['langId']);

        $token = array(
            'type' => 'open'
        );
        $gameHandler->sendToUser($user, $token);
    }
}