<?php
namespace GameServer;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Users
{
    private $_users = array();

    public function add($id)
    {
        $this->_users[] = $id;
    }

    public function remove($id)
    {
        foreach ($this->_users as $k => $v) {
            if ($v == $id) {
                unset($this->_users[$k]);
            }
        }
    }

    public function find($id)
    {
        foreach ($this->_users as $k => $v) {
            if ($v == $id) {
                return true;
            }
        }
    }

    public function count()
    {
        return count($this->_users);
    }
}

class Game
{
    private $_id;
    private $_Users;

    public function __construct($id)
    {
        $this->_id = $id;
        $this->_Users = new Users();
    }

    /**
     * @return Users
     */
    public function getUsers()
    {
        return $this->_Users;
    }

    public function getId()
    {
        return $this->_id;
    }
}

class Games
{
    private $_Games = array();

    public function add($id)
    {
        $this->_Games[] = new Game($id);
    }

    /**
     * @param $id
     * @return Game
     */
    public function get($id)
    {
        return $this->_Games[$id];
    }

    public function remove($userId)
    {
        foreach ($this->_Games as $id) {
            $game = $this->get($id);
            if ($game->getUsers()->find($userId)) {
                if ($game->getUsers()->count() <= 1) {
                    unset($this->_Games[$id]);
                }
            } else {
                $game->getUsers()->remove($userId);
            }
        }
    }

    public function find($userId)
    {
        foreach ($this->_Games as $k) {
            if ($this->get($k)->getUsers()->find($userId)) {
                return $this->get($k)->getId();
            }
        }
    }
}

class Handler implements MessageComponentInterface
{
    protected $clients;
    private $_games;

    private $_serialize = true;
    private $_blocking = true;
    private $_queue;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->_queue = msg_get_queue(123402);
        $this->_games = new Games();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $dataIn = json_decode($msg);
        if ($dataIn->type == 'open') {
            $data = array(
                'id' => $from->resourceId,
                'msg' => $dataIn
            );
            msg_send($this->_queue, 1, $data, $this->_serialize, $this->_blocking, $err);
        } else {
            if ($gameId = $this->_games->find($from->resourceId)) {
                $data = array(
                    'id' => $from->resourceId,
                    'msg' => $dataIn
                );
                msg_send($this->_queue, $gameId, $data, $this->_serialize, $this->_blocking, $err);
            }
        }
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
        $this->_games->remove($conn->resourceId);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}
