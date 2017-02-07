<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

include_once(APPLICATION_PATH . '/modules/cli/controllers/Contact.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Create.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Editor.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Friends.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Help.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Halloffame.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Load.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Messages.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Join.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Setup.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Single.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Tutorial.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Over.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Play.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Players.php');
include_once(APPLICATION_PATH . '/modules/cli/controllers/Profile.php');

class Cli_MainHandler extends WebSocketUriHandler
{
    private $_db;
    private $_help;
    private $_tutorial;

    public function __construct($logger)
    {
        $this->_db = Cli_Model_Database::getDb();
        parent::__construct($logger);
    }

    public function getDb()
    {
        return $this->_db;
    }

    public function addTutorial(Cli_Model_Tutorial $tutorial)
    {
        $this->_tutorial = $tutorial;
    }

    /**
     * @return Cli_Model_Tutorial
     */
    public function getTutorial()
    {
        return $this->_tutorial;
    }

    public function addHelp(Cli_Model_Help $help)
    {
        $this->_help = $help;
    }

    /**
     * @return Cli_Model_Help
     */
    public function getHelp()
    {
        return $this->_help;
    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $config = Zend_Registry::get('config');
        $dataIn = Zend_Json::decode($msg->getData());

        if ($config->debug) {
            echo('Cli_MainHandler ZAPYTANIE ');
            print_r($dataIn);
        }

        if (Zend_Registry::get('config')->debug) {
            print_r('Cli_MainHandler ZAPYTANIE ');
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
            } else {
                echo 'Method ' . $methodName . ' in class ' . $className . ' !exists' . "\n";
            }
        } else {
            echo 'Class ' . $className . ' !exists' . "\n";
        }
    }

    public function onDisconnect(WebSocketTransportInterface $user)
    {
        if (isset($user->parameters['playerId'])) {
            $mWebSocket = new Application_Model_Websocket($user->parameters['playerId'], $this->_db);
            $mWebSocket->disconnect($user->parameters['accessKey']);
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
            print_r('Cli_MainHandler ODPOWIEDŹ ');
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
