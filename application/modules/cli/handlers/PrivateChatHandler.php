<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

class Cli_PrivateChatHandler extends WebSocketUriHandler
{
    private $_db;
    private $_users = array();

    public function __construct($logger)
    {
        $this->_db = Cli_Model_Database::getDb();
        parent::__construct($logger);
    }

    public function getDb()
    {
        return $this->_db;
    }

    public function getUsers()
    {
        return $this->_users;
    }

    public function addUser($playerId, WebSocketTransportInterface $user)
    {
        $this->_users[$playerId] = $user;
    }

    /**
     * @param $playerId
     * @return Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     */
    public function getUser($playerId)
    {
        if (isset($this->_users[$playerId])) {
            return $this->_users[$playerId];
        }
    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());

        switch ($dataIn['type']) {
            case 'open':
                new Cli_Model_PrivateChatOpen($dataIn, $user, $this);
                break;
            case 'chat':
                new Cli_Model_PrivateChat($dataIn, $user, $this);
                break;
            case 'read':
                new Cli_Model_PrivateChatRead($dataIn, $user, $this);
                break;
        }
    }

    public function onDisconnect(WebSocketTransportInterface $user)
    {
        if (!isset($user->parameters['playerId'])) {
            return;
        }

        $mWebSocket = new Application_Model_Websocket($user->parameters['playerId'], $this->_db);
        $mWebSocket->disconnect($user->parameters['accessKey']);

        unset($this->_users[$user->parameters['playerId']]);
    }

    /**
     * @param $user
     * @param $msg
     */
    public function sendError($user, $msg)
    {
        $token = array(
            'type' => 'error',
            'msg' => $msg
        );

        $this->sendToUser($user, $token);
    }

    /**
     * @param $user
     * @param $token
     * @param null $debug
     */
    public function sendToUser(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $token, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ');
            print_r($token);
        }

        $user->sendString(Zend_Json::encode($token));
    }
}
