<?php
namespace GameSever;
class Test1 extends \Thread
{
    private $_id;

    public function __construct($data)
    {
        $this->_id = $data['id'];
    }

    public function run()
    {
        sleep(10);

        $context = new \ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://localhost:5555");

        $token = array(
            'token' => array('type' => 'test'),
            'id' => $this->_id
        );

//        $socket->send(json_encode($token));
        $socket->send(serialize($token));
    }
}