<?php

class Cli_Model_Setup
{
    private $_id;
    private $_isOpen = true;

    private $_users = array();
    private $_players = array();

    private $_gameMasterId;

    public function __construct($gameId, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $this->_l = new Coret_Model_Logger();

        $this->_id = $gameId;

        $mGame = new Application_Model_Game($this->_id, $db);
        $this->_gameMasterId = $mGame->getGameMasterId();
        if (empty($this->_gameMasterId)) {
            $this->setNewGameMaster($db);
            $this->_gameMasterId = $mGame->getGameMasterId();
        }
    }

    public function update($playerId, Cli_SetupHandler $handler, $close = false)
    {
        if ($close) {
            $token = array(
                'player' => array('playerId' => $playerId),
                'gameMasterId' => $this->_gameMasterId,
                'close' => 1,
                'type' => 'update'
            );
        } else {
            $token = array(
                'player' => $this->_players[$playerId],
                'gameMasterId' => $this->_gameMasterId,
                'type' => 'update'
            );
        }

        $handler->sendToChannel($handler->getGame($this->_id), $token);
    }

    public function getPlayerIdByMapPlayerId($mapPlayerId)
    {
        foreach ($this->_players as $playerId => $player) {
            if (isset($player['mapPlayerId']) && $player['mapPlayerId'] == $mapPlayerId) {
                return $playerId;
            }
        }
    }

    public function updatePlayerReady($playerId, $mapPlayerId)
    {
        $this->_players[$playerId]['mapPlayerId'] = $mapPlayerId;
    }

    public function isNoComputerColorInGame($mapPlayerId)
    {
        foreach ($this->_players as $playerId => $player) {
            if ($player['mapPlayerId'] == $mapPlayerId && $player['computer'] == false) {
                return true;
            }
        }
    }

    public function isPlayer($mapPlayerId)
    {
        foreach ($this->_players as $playerId => $player) {
            if (isset($player['mapPlayerId']) && $player['mapPlayerId'] == $mapPlayerId) {
                return true;
            }
        }
    }

    public function isGameMaster($playerId)
    {
        return $playerId == $this->_gameMasterId;
    }

    public function setNewGameMaster($db)
    {
        $this->_gameMasterId = $this->findNewGameMaster();
        $mGame = new Application_Model_Game($this->_id, $db);
        $mGame->setNewGameMaster($this->_gameMasterId);
    }

    public function findNewGameMaster()
    {
        reset($this->_users);
        return key($this->_users);
    }

    public function getGameMasterId()
    {
        return $this->_gameMasterId;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function addUser($playerId, Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $db)
    {
        $mPlayer = new Application_Model_Player($db);
        $player = $mPlayer->getPlayer($playerId);
        $this->_players[$playerId] = array(
            'playerId' => $player['playerId'],
//            'mapPlayerId' => $player['mapPlayerId'],
            'firstName' => $player['firstName'],
            'lastName' => $player['lastName']
        );
        $this->_users[$playerId] = $user;
    }

    public function removeUser(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mWebSocket = new Application_Model_Websocket($user->parameters['playerId'], $db);
        $mWebSocket->disconnect($user->parameters['accessKey']);
        unset($this->_users[$user->parameters['playerId']]);
    }

    public function getUsers()
    {
        return $this->_users;
    }

    public function getPlayers()
    {
        return $this->_players;
    }

    public function getIsOpen()
    {
        return $this->_isOpen;
    }

    public function setIsOpen($isOpen)
    {
        $this->_isOpen = $isOpen;
    }

    /**
     * @param Devristo\Phpws\Protocol\WebSocketTransportInterface $user
     * @return Cli_Model_Setup
     */
    static public function getSetup(Devristo\Phpws\Protocol\WebSocketTransportInterface $user)
    {
        return $user->parameters['game'];
    }
}