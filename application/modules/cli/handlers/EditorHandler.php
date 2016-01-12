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
class Cli_EditorHandler extends WebSocketUriHandler
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
            new Cli_Model_EditorOpen($dataIn, $user, $this);
            return;
        }

        if (!Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'Brak "playerId". Brak autoryzacji.');
            return;
        }

        switch ($dataIn['type']) {
            case 'add':
                switch ($dataIn['itemName']) {
                    case 'castle':
//                $mCastle = new Application_Model_Castle($this->_db);
//                $mMapCastles = new Application_Model_MapCastles($dataIn['mapId'], $this->_db);
//                $mapCastlesIds = $mMapCastles->getMapCastlesIds();
//                $castleId = $mCastle->getNextFreeCastleId($mapCastlesIds);
//                $mMapCastles->add($dataIn['x'], $dataIn['y'], $castleId);
                        break;
                    case 'tower':

                        break;
                    case 'ruin':

                        break;
                    case 'forest':

                        break;
                }
                break;

            case 'remove':
                $mMapCastles = new Application_Model_MapCastles($dataIn['mapId'], $this->_db);
                $mMapCastles->remove($dataIn['x'], $dataIn['y']);
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
