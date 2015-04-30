<?php

class Cli_Model_PrivateChatDelete
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
        if (!isset($dataIn['playerId'])) {
            throw new Exception('Brak "playerId"');
            return;
        }

        $db = $handler->getDb();
        $mFriends = new Application_Model_Friends($db);
        $mFriends->remove($user->parameters['playerId'], $dataIn['playerId']);
    }
}