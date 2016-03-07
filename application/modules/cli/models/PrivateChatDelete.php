<?php

class Cli_Model_PrivateChatDelete
{
    /**
     * Cli_Model_PrivateChatDelete constructor.
     * @param $dataIn
     * @param \Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @param Cli_PrivateChatHandler $handler
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