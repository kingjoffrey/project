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
    private $_editor;

    public function __construct($logger)
    {
        $this->_db = Cli_Model_Database::getDb();
        parent::__construct($logger);
    }

    public function getDb()
    {
        return $this->_db;
    }

    public function addEditor(Cli_Model_Editor $editor)
    {
        $this->_editor = $editor;
    }

    public function removeEditor()
    {
        $this->_editor = null;
    }

    /**
     * @return Cli_Model_Editor
     */
    public function getEditor()
    {
        return $this->_editor;
    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());
        if (Zend_Registry::get('config')->debug) {
            print_r('ZAPYTANIE ');
            print_r($dataIn);
        }

        if ($dataIn['type'] == 'open') {
            new Cli_GeneratorOpen($dataIn, $user, $this);
            return;
        }

        if (!Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'Brak "playerId". Brak autoryzacji.');
            return;
        }

        switch ($dataIn['type']) {
            case 'save':
                $map = str_replace('data:image/png;base64,', '', $dataIn['map']);
                $map = str_replace(' ', '+', $map);
                $file = APPLICATION_PATH . '/../public/img/maps/' . $dataIn['mapId'] . '.png';
                $success = file_put_contents($file, base64_decode($map));
                $mapFields = new Application_Model_MapFields($dataIn['mapId'], $this->_db);
                foreach ($dataIn['fields'] as $y => $row) {
                    foreach ($row as $x => $type) {
                        $mapFields->add($x, $y, $type);
                    }
                }
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
    public function sendToUser(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, $token, $debug = null)
    {
        if ($debug || Zend_Registry::get('config')->debug) {
            print_r('ODPOWIEDŹ');
            print_r($token);
        }

        $user->sendString(Zend_Json::encode($token));
    }
}
