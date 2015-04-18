<?php

class Cli_Model_PrivateChatOpen
{
    /**
     * @param $dataIn
     * @param Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Zend_Db_Adapter_Pdo_Pgsql $db
     * @param Cli_GameHandler $handler
     * @throws Exception
     */
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_PrivateChatHandler $handler)
    {
        if (!isset($dataIn['playerId']) || !isset($dataIn['langId'])) {
            throw new Exception('Brak "playerId" lub "langId');
            return;
        }

        $db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            throw new Exception($user, 'Brak uprawnień!');
            return;
        }

        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];
        $user->parameters['name'] = $dataIn['name'];

        $handler->addUser($dataIn['playerId'], $user);

//        $token = array(
//            'type' => 'open'
//        );
//        $handler->sendToUser($user, $token);
    }
}