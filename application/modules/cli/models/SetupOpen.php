<?php

class Cli_Model_SetupOpen
{
    /**
     * Cli_Model_SetupOpen constructor.
     * @param $dataIn
     * @param \Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Cli_SetupHandler $handler
     */
    public function __construct($dataIn, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_SetupHandler $handler)
    {
        if (!isset($dataIn['gameId'])) {
            throw new Exception('Brak "gameId"');
        }
        if (!isset($dataIn['playerId'])) {
            throw new Exception('Brak "playerId"');
        }
        if (!isset($dataIn['langId'])) {
            throw new Exception('Brak langId');
        }

        $db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);
        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            throw new Exception('Brak uprawnień!');
        }

        if (!($user->parameters['game'] = $handler->getGame($dataIn['gameId']))) {
            echo 'not set' . "\n";
            $handler->addGame($dataIn['gameId'], new Cli_Model_Setup($dataIn['gameId'], $db));
            $user->parameters['game'] = $handler->getGame($dataIn['gameId']);
        }

        $setup = Cli_Model_Setup::getSetup($user);
        $setup->addUser($dataIn['playerId'], $user, $db, $handler);

        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['name'] = $dataIn['name'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];

        foreach ($setup->getUsers() as $u) {
            $setup->update($u->parameters['playerId'], $handler);
        }

        $token = array(
            'type' => 'open'
        );

        $handler->sendToUser($user, $token);
    }
}