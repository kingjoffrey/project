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
    private $_portMax = 65535;
    private $_projectDirName;

    public function __construct($logger)
    {
        parent::__construct($logger);
        $this->_mainPort = Zend_Registry::get('config')->websockets->aPort;
        $this->_portMax = $this->_portMax - $this->_mainPort;
        $this->_projectDirName = Zend_Registry::get('config')->projectDir->name;
    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());
        if (Zend_Registry::get('config')->debug) {
            print_r('Cli_ExecHandler ZAPYTANIE ');
            print_r($dataIn);
        }

        if (!isset($dataIn['gameId'])) {
            echo('EXEC: brak "gameId"');
            return;
        }

        new Cli_Model_ExecOpen($dataIn, $user, $this);

        if (!Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'Brak autoryzacji.');
            return;
        }

        // game exist, send game port to user
        if ($Game = $this->findGame($dataIn['gameId'])) {
            if (!$Game->findPlayer($user->getId())) {
                $Player = new Player($user->getId());
                $Game->addPlayer($Player);
            }

            $execPort = $this->_mainPort + $Game->getPort();

            $token = array(
                'type' => 'port',
                'port' => $execPort
            );

            $user->sendString(Zend_Json::encode($token));
        } else {
            if (isset($dataIn['gameId']) && (is_string($dataIn['gameId']) || is_int($dataIn['gameId']))) {
                $port = $this->initPort();
                $execPort = $this->_mainPort + $port;
                $gameId = (int)$dataIn['gameId'];
                exec('/usr/bin/php ~/' . $this->_projectDirName . '/scripts/gameWSServer.php ' . $gameId . ' ' . $execPort . ' >>~/' . $this->_projectDirName . '/log/' . $gameId . '.log 2>&1 &');

                $this->addGame($dataIn['gameId'], $user->getId(), $port);

                $token = array(
                    'type' => 'port',
                    'port' => $execPort
                );

                $user->sendString(Zend_Json::encode($token));
            } else {
                echo gettype($dataIn['gameId']) . "\n";
                echo '$dataIn gameId not int' . "\n";
                print_r($dataIn);
            }
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
        if (isset($user->parameters['playerId'])) {
            unset($user->parameters['playerId']);
        }
        if (isset($user->parameters['accessKey'])) {
            unset($user->parameters['accessKey']);
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
            print_r('Cli_ExecHandler ODPOWIEDŹ ');
            print_r($token);
        }

        $user->sendString(Zend_Json::encode($token));
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

    /**
     * @param Game $Game
     * @return bool
     */
    public function removeGame(Game $Game)
    {
        foreach ($this->_games as $key1 => $G) {
            if ($Game == $G) {
                $port = $Game->getPort();
                foreach ($this->_ports as $key2 => $p) {
                    if ($p == $port) {
                        unset($this->_ports[$key2]);
                    }
                }
                $Game = null;
                unset($this->_games[$key1]);
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
        $port = current($this->_ports) + 1;
        if ($port > $this->_portMax) {
            $endOfLoop = false;
            $firsAvailablePort = 1;
            foreach ($this->_ports as $key => $p) {
                if ($p > $firsAvailablePort) {
                    $port = $firsAvailablePort;
                    $endOfLoop = true;
                    break;
                } else {
                    $firsAvailablePort++;
                }
            }
            if ($firsAvailablePort <= $this->_portMax) {
                $port = $firsAvailablePort;
                $endOfLoop = true;
            }
            if (!$endOfLoop) {
                throw new Exception('Nie ma wolnego portu!');
            }
        }
        $this->_ports[] = $port;
        return $port;
    }
}
