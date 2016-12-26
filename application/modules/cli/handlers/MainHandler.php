<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

include_once(APPLICATION_PATH . '/modules/cli/controllers/Editor.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Game.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Help.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Halloffame.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Index.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Load.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Messages.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/New.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Tutorial.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Over.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Play.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Players.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Profile.php');

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

        $className = ucfirst($dataIn['type']) . 'Controller';
        if (class_exists($className)) {
            $controller = new $className();
            $methodName = $dataIn['action'];
            if (method_exists($controller, $methodName)) {
                if (!isset($dataIn['params'])) {
                    $dataIn['params'] = null;
                }
                $controller->$methodName($user, $this, $dataIn['params']);
            }
        } else {
            print_r($dataIn);
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
