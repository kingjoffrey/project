<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

class Cli_TestHandler extends WebSocketUriHandler
{
    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());

        switch ($dataIn['type']) {
            case 'test1':
                $queue = msg_get_queue(123402);
                msg_send($queue, 1, 'test', false, false, $err);
                break;
            case 'test2':
                $queue = msg_get_queue(123402);
                msg_send($queue, 2, 'test', false, false, $err);
                break;
            case 'proxy':
                $user->sendString($dataIn['token']);
                break;
        }
    }
}
