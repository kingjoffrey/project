<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

class Cli_NewHandler extends WebSocketUriHandler
{
    private $_db;
    private $_new;

    public function __construct($logger)
    {
        $this->_db = Cli_Model_Database::getDb();
        parent::__construct($logger);
    }

    public function getDb()
    {
        return $this->_db;
    }

    public function addNew(Cli_Model_New $new)
    {
        $this->_new = $new;
    }

    public function removeNew()
    {
        $this->_new = null;
    }

    /**
     * @return Cli_Model_New
     */
    public function getNew()
    {
        return $this->_new;
    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());

        switch ($dataIn['type']) {
            case 'open':
                new Cli_Model_NewOpen($dataIn, $user, $this);
                break;
            case 'remove':
                $token = array(
                    'type' => 'remove',
                    'gameId' => $dataIn['gameId']
                );
                $this->sendToChannelExceptUser($user, $this->getNew(), $token);
                break;
            default:
                print_r($dataIn);
        }
    }

    public function onDisconnect(WebSocketTransportInterface $user)
    {
        $new = Cli_Model_New::getNew($user);
        if ($new) {
            $new->removeUser($user, $this->_db);

            if ($new->getUsers()) {
                if (isset($user->parameters['gameId'])) { //setup
                    $new->getGame($user->parameters['gameId'])->removePlayer($user->parameters['playerId']);
                    if (!$new->getGame($user->parameters['gameId'])->getPlayers()) {
                        $new->removeGame($user->parameters['gameId']);
                        $token = array(
                            'type' => 'removeGame',
                            'gameId' => $user->parameters['gameId']
                        );

                        $this->sendToChannelExceptPlayers($new, $token);
                    }
                    $token = array(
                        'type' => 'removePlayer',
                        'playerId' => $user->parameters['playerId'],
                        'gameId' => $user->parameters['gameId']
                    );

                    $this->sendToChannelExceptPlayers($new, $token);
                } else { //new
                    $token = array(
                        'type' => 'close',
                        'playerId' => $user->parameters['playerId']
                    );
                    $this->sendToChannelOnlyPlayers($new, $token);
                }
            } else {
                $this->removeNew();
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
     * @param Cli_Model_New $new
     * @param $token
     * @param null $debug
     * @throws Zend_Exception
     */
    public function sendToChannel(Cli_Model_New $new, $token, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }

        foreach ($new->getUsers() AS $user) {
            $this->sendToUser($user, $token);
        }
    }

    /**
     * @param WebSocketTransportInterface $u
     * @param Cli_Model_New $new
     * @param $token
     * @param null $debug
     * @throws Zend_Exception
     */
    public function sendToChannelExceptUser(Devristo\Phpws\Protocol\WebSocketTransportInterface $u, Cli_Model_New $new, $token, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }

        foreach ($new->getUsers() AS $user) {
            if ($user == $u) {
                continue;
            }
            $this->sendToUser($user, $token);
        }
    }

    public function sendToChannelExceptPlayers(Cli_Model_New $new, $token, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }

        foreach ($new->getUsers() AS $playerId => $user) {
            if (isset($user->parameters['gameId']) && $new->getGame($user->parameters['gameId'])->getPlayer($playerId)) {
                continue;
            }
            $this->sendToUser($user, $token);
        }
    }

    public function sendToChannelOnlyPlayers(Cli_Model_New $new, $token, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }

        foreach ($new->getUsers() AS $playerId => $user) {
            if (isset($user->parameters['gameId']) && $new->getGame($user->parameters['gameId'])->getPlayer($playerId)) {
                $this->sendToUser($user, $token);
            }
        }
    }
}
