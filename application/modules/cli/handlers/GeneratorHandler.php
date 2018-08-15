<?php
use Devristo\Phpws\Messaging\WebSocketMessageInterface;
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
use Devristo\Phpws\Server\UriHandler\WebSocketUriHandler;

/**
 * This resource handler will respond to all messages sent to /public on the socketserver below
 *
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Cli_GeneratorHandler extends WebSocketUriHandler
{
    private $_db;

    public function __construct($logger)
    {
        $this->_db = Cli_Model_Database::getDb();
        parent::__construct($logger);
    }

    public function getDb()
    {
        return $this->_db;
    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());
        if (Zend_Registry::get('config')->debug) {
            print_r('Cli_GeneratorHandler ZAPYTANIE ');
            print_r($dataIn);
        }

        if ($dataIn['type'] == 'open') {
            new Cli_Model_GeneratorOpen($dataIn, $user, $this);
            return;
        }

        if (!Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $l = new Coret_Model_Logger('Cli_GeneratorHandler');
            $l->log('Brak autoryzacji.');
            return;
        }

        switch ($dataIn['type']) {
            case 'publish':
                $this->sendToUser($user, $this->publish($dataIn, $this->_db));
                break;
            case 'create':
                $g = new Cli_Model_Generator();
                $this->sendToUser($user, $g->create($dataIn, $this->_db, $user->parameters['playerId']));
                break;
            case 'mirror':
                $this->sendToUser($user, $this->mirror($dataIn, $this->_db, $user->parameters['playerId']));
                break;
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
            print_r('Cli_GeneratorHandler ODPOWIEDŹ ');
            print_r($token);
        }

        $user->sendString(Zend_Json::encode($token));
    }
}
