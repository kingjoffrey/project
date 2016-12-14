<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

class Cli_MainHandler extends WebSocketUriHandler
{
    private $_db;
    private $_menu;
    private $_view;

    public function __construct($logger)
    {
        $this->_db = Cli_Model_Database::getDb();
        parent::__construct($logger);

        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

        $this->_menu = array(
            'play' => $adapter->translate('Play'),
            'load' => $adapter->translate('Load game'),
            'halloffame' => $adapter->translate('Hall of Fame'),
//            'hero' => $adapter->translate('Hero'),
            'players' => $adapter->translate('Players'),
            'profile' => $adapter->translate('Profile'),
            'help' => $adapter->translate('Help'),
//            'stats' => $adapter->translate('Stats'),
            'editor' => $adapter->translate('Map editor'),
//            'market' => $adapter->translate('Market'),
        );

        $this->_view = new Zend_View();
    }

    public function getDb()
    {
        return $this->_db;
    }

    /**
     * @return array
     */
    public function menu()
    {
        return $this->_menu;
    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());
        if (Zend_Registry::get('config')->debug) {
            print_r('ZAPYTANIE ');
            print_r($dataIn);
        }

        if ($dataIn['type'] == 'open') {
            new Cli_Model_MainOpen($dataIn, $user, $this);
            return;
        }

        if (!Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'Brak autoryzacji.');
            return;
        }

        switch ($dataIn['type']) {
            case 'editor':
                switch ($dataIn['action']) {
                    case 'create';
                        $this->_view->form = new Application_Form_Createmap ();
                        $this->_view->formIsValid = false;
                        if ($this->_request->isPost()) {
                            if ($this->_view->form->isValid($this->_request->getPost())) {
                                $this->_view->formIsValid = true;
                                $mMap = new Application_Model_Map ();
                                $this->_view->mapId = $mMap->createMap($this->_view->form->getValues(), Zend_Auth::getInstance()->getIdentity()->playerId);

                                $mSide = new Application_Model_Side();

                                $mMapPlayers = new Application_Model_MapPlayers($this->_view->mapId);
                                $mMapPlayers->create($mSide->getWithLimit($this->_request->getParam('maxPlayers')), $this->_view->mapId);

                                $this->_view->mapSize = $this->_request->getParam('mapSize');

                                $this->_helper->layout->setLayout('empty');

                                $this->_view->headScript()->appendFile('/js/mapgenerator/init.js?v=' . Zend_Registry::get('config')->version);
                                $this->_view->headScript()->appendFile('/js/mapgenerator/diamondsquare.js?v=' . Zend_Registry::get('config')->version);
                                $this->_view->headScript()->appendFile('/js/mapgenerator/mapgenerator.js?v=' . Zend_Registry::get('config')->version);
                                $this->_view->headScript()->appendFile('/js/mapgenerator/websocket.js?v=' . Zend_Registry::get('config')->version);
                            }
                        }
                        break;
                    case 'edit';
                        break;
                    case 'test';
                        break;
                    default:
                        $mMap = new Application_Model_Map (0, $this->_db);
                        $token = array(
                            'type' => 'controller',
                            'data' => $mMap->getPlayerMapList($user->parameters['playerId'])
                        );
                        $this->sendToUser($user, $token);
                        break;
                }

                break;
            default:
                print_r($dataIn);
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
}
