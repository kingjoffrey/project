<?php
namespace GameSever;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Handler implements MessageComponentInterface
{
    protected $clients;
    private $_serialize = true;
    private $_blocking = true;
    private $_queue;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->_queue = msg_get_queue(123402);
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = array(
            'id' => $from->resourceId,
            'msg' => json_decode($msg)
        );
        msg_send($this->_queue, 1, $data, $this->_serialize, $this->_blocking, $err);
    }

    public function onZMQ($msg)
    {
        $dataIn = unserialize($msg);

        foreach ($this->clients as $client) {
            if ($client->resourceId == $dataIn['id']) {
                $client->send(json_encode($dataIn['token']));
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}
