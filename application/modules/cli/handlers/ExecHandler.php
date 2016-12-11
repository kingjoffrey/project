<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

class Player
{
    private $_playerId;

    public function __construct($id)
    {
        $this->_playerId = $id;
    }

    public function getId()
    {
        return $this->_playerId;
    }
}

class Game
{
    private $_gameId;
    private $_port;
    private $_players = array();

    public function __construct($gameId, $port)
    {
        $this->_gameId = $gameId;
        $this->_port = $port;
    }

    public function addPlayer(Player $Player)
    {
        $this->_players[] = $Player;
    }

    /**
     * @param $key
     * @return Player
     */
    public function getPlayer($key)
    {
        return $this->_players[$key];
    }

    public function countPlayers()
    {
        return count($this->_players);
    }

    /**
     * @param $id
     * @return Player
     */
    public function findPlayer($id)
    {
        foreach (array_keys($this->_players) as $key) {
            $Player = $this->getPlayer($key);
            if ($id == $Player->getId()) {
                return $Player;
            }
        }
    }

    public function removePlayer(Player $Player)
    {
        foreach ($this->_players as $key => $P) {
            if ($Player == $P) {
                $Player = null;
                unset($this->_players[$key]);
                return true;
            }
        }
    }

    public function getId()
    {
        return $this->_gameId;
    }

    public function getPort()
    {
        return $this->_port;
    }
}

class Cli_ExecHandler extends WebSocketUriHandler
{
    private $_games = array();
    private $_ports = array();
    private $_mainPort;

    public function __construct($logger)
    {
        parent::__construct($logger);
        $this->_mainPort = Zend_Registry::get('config')->websockets->aPort;

    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());

        if (!isset($dataIn['gameId'])) {
            throw new Exception('Brak "gameId"');
            return;
        }

        // game exist, send game port to user
        if ($Game = $this->findGame($dataIn['gameId'])) {
            if (!$Game->findPlayer($user->getId())) {
                $Player = new Player($user->getId());
                $Game->addPlayer($Player);
            }

            $pcntlPort = $this->_mainPort + $Game->getPort();

            $token = array(
                'type' => 'port',
                'port' => $pcntlPort
            );

            $user->sendString(Zend_Json::encode($token));
        } else {
            $port = $this->initPort();
            $pcntlPort = $this->_mainPort + $port;
            $user->parameters['gameId'] = $dataIn['gameId'];
            $this->addGame($dataIn['gameId'], $user->getId(), $port);
            $token = array(
                'type' => 'port',
                'port' => $pcntlPort
            );

            $user->sendString(Zend_Json::encode($token));
            exec('/usr/bin/php /home/idea/WOF/scripts/gameWSServer.php ' . $dataIn['gameId'] . ' ' . $pcntlPort . ' >/home/idea/WOF/log/' . $dataIn['gameId'] . '.log 2>&1 &');
        }
    }

    public function onDisconnect(WebSocketTransportInterface $user)
    {
        if ($Game = $this->findGame($user->parameters['gameId'])) {
            if ($Game->countPlayers() > 1) {
                if ($Player = $Game->findPlayer($user->getId())) {
                    $Game->removePlayer($Player);
                }
            } else {
                $this->removeGame($Game);
            }
        }
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

    public function addGame($gameId, $userId, $port)
    {
        $Game = new Game($gameId, $port);
        $Player = new Player($userId);
        $Game->addPlayer($Player);
        $this->_games[] = $Game;
    }

    /**
     * @param $key
     * @return Game
     */
    public function getGame($key)
    {
        return $this->_games[$key];
    }

    public function removeGame(Game $Game)
    {
        foreach ($this->_games as $key => $G) {
            if ($Game == $G) {
                $Game = null;
                unset($this->_games[$key]);
                return true;
            }
        }
    }

    /**
     * @param $id
     * @return Game
     */
    public function findGame($id)
    {
        foreach (array_keys($this->_games) as $key) {
            $Game = $this->getGame($key);
            if ($Game->getId() == $id) {
                return $Game;
            }
        }
    }

    public function initPort()
    {
        end($this->_ports);
        $maxPort = current($this->_ports);
//        $length = count($this->_ports);
//        if($maxPort<$length){
//
//        }
        $port = $maxPort + 1;
        $this->_ports[] = $port;
        return $port;
    }
}
