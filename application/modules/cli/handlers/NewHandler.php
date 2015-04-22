<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

class Cli_NewHandler extends WebSocketUriHandler
{
    private $_db;
    private $_games = array();

    public function __construct($logger)
    {
        $this->_db = Cli_Model_Database::getDb();
        parent::__construct($logger);
    }

    public function getDb()
    {
        return $this->_db;
    }

    public function addGame($gameId, Cli_Model_Setup $game)
    {
        $this->_games[$gameId] = $game;
    }

    public function removeGame($gameId)
    {
        $this->_games[$gameId] = null;
        unset($this->_games[$gameId]);
    }

    /**
     * @param $gameId
     * @return Cli_Model_Setup
     */
    public function getGame($gameId)
    {
        if (isset($this->_games[$gameId])) {
            return $this->_games[$gameId];
        }
    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());

        switch ($dataIn['type']) {
            case 'open':
                new Cli_Model_NewOpen($dataIn, $user, $this);
                break;
        }
    }

    public function onDisconnect(WebSocketTransportInterface $user)
    {
        $setup = Cli_Model_Setup::getSetup($user);
        if ($setup) {
            $setup->removeUser($user, $this->_db);

            $token = array(
                'type' => 'close',
                'playerId' => $user->parameters['playerId']
            );

            $this->sendToChannel($setup, $token);

            if (!$setup->getUsers()) {
                $this->removeGame($setup->getId());
            }
        }
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

    /**
     * @param Cli_Model_Setup $setup
     * @param $token
     * @param null $debug
     * @throws Zend_Exception
     */
    public function sendToChannel(Cli_Model_Setup $setup, $token, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }

        foreach ($setup->getUsers() AS $user) {
            $this->sendToUser($user, $token);
        }
    }
}
