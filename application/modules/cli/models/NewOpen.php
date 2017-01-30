<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
class Cli_Model_NewOpen
{
    /**
     * Cli_Model_NewOpen constructor.
     * @param $dataIn
     * @param WebSocketTransportInterface $user
     * @param Cli_NewHandler $handler
     */
    public function __construct($dataIn, WebSocketTransportInterface $user, Cli_NewHandler $handler)
    {
        if (!isset($dataIn['playerId'])) {
            echo('Brak "playerId"');
            return;
        }
        if (!isset($dataIn['langId'])) {
            echo('Brak langId');
            return;
        }

        $db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            echo ('Brak uprawnień (playerId=' . $dataIn['playerId'] . ')') . "\n";
            return;
        }

        if (!($user->parameters['new'] = $handler->getNew())) {
            echo 'not set' . "\n";
            $handler->addNew(new Cli_Model_New());
            $user->parameters['new'] = $handler->getNew();
        }

        $new = Cli_Model_New::getNew($user);
        $new->addUser($dataIn['playerId'], $user);

        $token = array(
            'type' => 'games',
            'games' => $new->gamesToArray()
        );
        $handler->sendToUser($user, $token);

        $user->parameters['name'] = $dataIn['name'];
        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];
    }
}