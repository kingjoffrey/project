<?php

class Cli_Model_HelpOpen
{

    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_HelpHandler $handler)
    {
        if (!isset($dataIn['playerId'])) {
            $handler->sendError($user, 'Brak "playerId"');
            return;
        }

        $db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            throw new Exception('Brak uprawnień!');
        }

        Zend_Registry::set('id_lang', $dataIn['langId']);

        if (!$help = $handler->get()) {
            echo 'not set' . "\n";
            $handler->add(new Cli_Model_Help($db));
            $help = $handler->get();
        }

//
        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];

        $token = $help->toArray();
        $token['type'] = 'open';

        $handler->sendToUser($user, $token);
    }
}
