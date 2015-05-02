<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

class Cli_SetupHandler extends WebSocketUriHandler
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
                new Cli_Model_SetupOpen($dataIn, $user, $this);
                break;
            case 'team':
                $this->sendToChannel(Cli_Model_Setup::getSetup($user), array(
                    'type' => 'team',
                    'mapPlayerId' => $dataIn['mapPlayerId'],
                    'teamId' => $dataIn['teamId']
                ));
                break;

            case 'start':
                new Cli_Model_SetupStart($dataIn, $user, $this);
                break;

            case 'change':
                new Cli_Model_SetupChange($dataIn, $user, $this);
                break;

            case 'chat':
                $token = array(
                    'type' => 'chat',
                    'playerId' => $user->parameters['playerId'],
                    'name' => $user->parameters['name'],
                    'msg' => $dataIn['msg']
                );
                $this->sendToChannelExceptUser($user, $token);
                break;
        }
    }

    public function onDisconnect(WebSocketTransportInterface $user)
    {
        $setup = Cli_Model_Setup::getSetup($user);
        if ($setup) {
            $setup->removeUser($user, $this->_db);

            if ($setup->getUsers()) {
                if (!$setup->getIsOpen()) {
                    return;
                }
                if ($setup->getGameMasterId() == $user->parameters['playerId']) {
                    $setup->setNewGameMaster($this->_db);
                }
                $token = array(
                    'type' => 'close',
                    'playerId' => $user->parameters['playerId']
                );
                $this->sendToChannel($setup, $token);
            } else {
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

    public function sendToChannelExceptUser(Devristo\Phpws\Protocol\WebSocketTransportInterface $u, $token, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }

        $setup = Cli_Model_Setup::getSetup($u);

        foreach ($setup->getUsers() AS $user) {
            if ($u == $user) {
                continue;
            }
            $this->sendToUser($user, $token);
        }
    }
}
