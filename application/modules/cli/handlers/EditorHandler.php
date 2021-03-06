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
    private $_editors;

    public function __construct($logger)
    {
        $this->_db = Cli_Model_Database::getDb();
        parent::__construct($logger);
    }

    public function getDb()
    {
        return $this->_db;
    }

    public function addEditor($mapId, Cli_Model_Editor $editor)
    {
        $this->_editors[$mapId] = $editor;
    }

    public function removeEditor($mapId)
    {
        unset($this->_editors[$mapId]);
    }

    /**
     * @param $mapId
     * @return Cli_Model_Editor
     */
    public function getEditor($mapId)
    {
        if (isset($this->_editors[$mapId])) {
            return $this->_editors[$mapId];
        }
    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());
        if (Zend_Registry::get('config')->debug) {
            echo('Cli_EditorHandler ZAPYTANIE ');
            print_r($dataIn);
        }

        if ($dataIn['type'] == 'open') {
            new Cli_Model_EditorOpen($dataIn, $user, $this);
            return;
        }

        if (!Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $l = new Coret_Model_Logger('Cli_EditorHandler');
            $l->log('Brak autoryzacji.');
            return;
        }

        switch ($dataIn['type']) {
            case 'add':
                $this->sendToUser($user, $this->getEditor($dataIn['mapId'])->add($dataIn, $this->_db));
                break;

            case 'editRuin':
                $this->sendToUser($user, $this->getEditor($dataIn['mapId'])->editRuin($dataIn, $this->_db));
                break;

            case 'editCastle':
                $this->sendToUser($user, $this->getEditor($dataIn['mapId'])->editCastle($dataIn, $this->_db));
                break;

            case 'remove':
                $this->sendToUser($user, $this->getEditor($dataIn['mapId'])->remove($dataIn, $this->_db));
                break;

            case 'up':
                $this->sendToUser($user, $this->getEditor($dataIn['mapId'])->up($dataIn, $this->_db));
                break;

            case 'down':
                $this->sendToUser($user, $this->getEditor($dataIn['mapId'])->down($dataIn, $this->_db));
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
            print_r('Cli_EditorHandler ODPOWIEDŹ ');
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
