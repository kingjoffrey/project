<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

class Cli_NewHandler extends WebSocketUriHandler
{
    private $_db;
    private $_new;
    private $_setupGames = array();

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


    public function addSetupGame($gameId, SetupGame $game)
    {
        $this->_setupGames[$gameId] = $game;
    }

    public function removeSetupGame($gameId)
    {
        $this->_setupGames[$gameId] = null;
        unset($this->_setupGames[$gameId]);
    }

    /**
     * @param $gameId
     * @return SetupGame
     */
    public function getSetupGame($gameId)
    {
        if (isset($this->_setupGames[$gameId])) {
            return $this->_setupGames[$gameId];
        }
    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());

        if ($dataIn['type'] == 'open') {
            new Cli_Model_NewOpen($dataIn, $user, $this);
            return;
        }

        if (!Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'Brak autoryzacji.');
            return;
        }

        switch ($dataIn['type']) {
            case 'setup':
                new Cli_Model_SetupInit($dataIn, $user, $this);
                break;
            case 'team':
                $this->sendToChannel(SetupGame::getSetup($user), array(
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

            case 'remove':
                $token = array(
                    'type' => 'removeGame',
                    'gameId' => $dataIn['gameId']
                );
                $this->sendToChannelExceptPlayers($this->getNew(), $token);
                break;
            default:
                print_r($dataIn);
        }
    }

    public function onDisconnect(WebSocketTransportInterface $user)
    {
        $new = Cli_Model_New::getNew($user);
        if ($new) {
            $new->removeUser($user->parameters['playerId']);
            $users = $new->getUsers();
            if ($users) {
                if (isset($user->parameters['gameId'])) { //setup
                    $game = $new->getGame($user->parameters['gameId']);
                    $game->removePlayer($user->parameters['playerId']);

                    $players = $game->getPlayers();

                    if (!$players) {
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
                        'id' => $user->parameters['playerId'],
                        'name' => $user->parameters['name']
                    );
                    $this->sendToChannelOnlyPlayers($new, $token);
                }
            } else {
                $this->removeNew();
            }
        }

        $setup = SetupGame::getSetup($user);
        if ($setup) {
            $setup->removeUser($user->parameters['playerId']);

            if ($setup->getGameMasterId() == $user->parameters['playerId']) {
                $setup->update($user->parameters['playerId'], $this, 1);
                $this->removeSetupGame($setup->getGameId());
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
     * @param WebSocketTransportInterface $user
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

    /**
     * @param $new
     * @param $token
     * @param null $debug
     */
    public function sendToChannel($new, $token, $debug = null)
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
    public function sendToChannelExceptUser(WebSocketTransportInterface $u, Cli_Model_New $new, $token, $debug = null)
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

    public function sendToChannelExceptPlayersAndMe($myId, Cli_Model_New $new, $token, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }

        foreach ($new->getUsers() AS $playerId => $user) {
            if ($playerId == $myId) {
                continue;
            }
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
