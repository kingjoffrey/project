<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

class Cli_PrivateChatHandler extends WebSocketUriHandler
{
    private $_db;
    private $_users = array();
    private $_players = array();

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
     * @return WebSocketTransportInterface $user
     */
    public function getUser($playerId)
    {
        if (isset($this->_users[$playerId])) {
            return $this->_users[$playerId];
        }
    }


    public function addFriends($playerId)
    {
        $mFriends = new Application_Model_Friends($this->_db);
        $this->_players[$playerId] = $mFriends->getFriendsIds($playerId);
    }

    public function getFriends($playerId)
    {
        if (isset($this->_players[$playerId])) {
            return $this->_players[$playerId];
        } else {
            return array();
        }
    }

    public function removeFriends($playerId)
    {
        if (isset($this->_players[$playerId])) {
            unset($this->_players[$playerId]);
        }
    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());
        if (Zend_Registry::get('config')->debug) {
            echo(get_class($this) . ' ZAPYTANIE ');
            print_r($dataIn);
        }

        if ($dataIn['type'] == 'open') {
            new Cli_Model_PrivateChatOpen($dataIn, $user, $this);
            return;
        }

        if (!Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $l = new Coret_Model_Logger(get_class($this));
            $l->log('Brak autoryzacji.');
            return;
        }

        switch ($dataIn['type']) {
            case 'chat':
                new Cli_Model_PrivateChat($dataIn, $user, $this);
                break;
            case 'threads':
                $db = $this->getDb();

                if (!isset($dataIn['page'])) {
                    $dataIn['page'] = 1;
                }

                $mPrivateChat = new Application_Model_PrivateChat($user->parameters['playerId'], $db);

                $threads = array();

                $mPlayer = new Application_Model_Player($db);
                $paginator = $mPlayer->getPlayersNames($mPrivateChat->getThreads(), $dataIn['page'], $user->parameters['playerId']);

                foreach ($paginator as $row) {
                    $threads[$row['playerId']] = array(
                        'name' => $row['name'],
                        'unread' => $mPrivateChat->getThreadUnreadMessageCount($row['playerId'])
                    );
                }

                $token = array(
                    'type' => 'threads',
                    'threads' => $threads
                );

                $this->sendToUser($user, $token);
                break;
            case 'conversation':
                if (!isset($dataIn['id'])) {
                    echo 'brak id';
                    return;
                }

                $db = $this->getDb();

                $mPrivateChat = new Application_Model_PrivateChat($user->parameters['playerId'], $db);
                $chatHistory = $mPrivateChat->getChatHistoryMessages($dataIn['id']);

                $messages = array();
                foreach ($chatHistory as $row) {
                    $messages[] = array(
                        'date' => Coret_View_Helper_Formatuj::date($row['date']),
                        'name' => $row['firstName'] . ' ' . $row['lastName'],
                        'message' => $row['message']
                    );
                }

                $messages = array_reverse($messages);
                $token = array(
                    'type' => 'conversation',
                    'messages' => $messages
                );

                $this->sendToUser($user, $token);
                break;
        }
    }

    public function onDisconnect(WebSocketTransportInterface $user)
    {
        if (!isset($user->parameters['playerId'])) {
            return;
        }

        $token = array(
            'type' => 'close',
            'id' => $user->parameters['playerId']
        );

        foreach ($this->getFriends($user->parameters['playerId']) AS $friend) {
            foreach ($this->getUsers() as $u) {
                if ($friend['friendId'] == $u->parameters['playerId']) {
                    $this->sendToUser($u, $token);
                }
            }
        }

        $this->removeFriends($user->parameters['playerId']);
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
    public function sendToUser(WebSocketTransportInterface $user, $token, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ');
            print_r($token);
        }

        $user->sendString(Zend_Json::encode($token));
    }
}