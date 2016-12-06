<?php
namespace GameServer;
class Test1 extends \Thread
{
    private $_id;
    private $_object;

    public function __construct($id, A $object)
    {
        $this->_id = $id;
        $this->_object = $object;
    }

    public function run()
    {
        $this->_object->increment();
        sleep(10);

        $context = new \ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://localhost:5555");

        $token = array(
            'token' => array(
                'type' => 'test',
                'i' => $this->_object->getI()
            ),
            'id' => $this->_id
        );

        $socket->send(serialize($token));
    }
}