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

    public function __construct($logger)
    {
        $this->_db = Cli_Model_Database::getDb();
        parent::__construct($logger);
    }

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());
        if (Zend_Registry::get('config')->debug) {
            print_r('ZAPYTANIE ');
            print_r($dataIn);
        }

        if ($dataIn['type'] == 'open') {
            if (!isset($dataIn['playerId'])) {
                $this->sendError($user, 'Brak "playerId"');
                return;
            }

            $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $this->_db);

            if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $this->_db)) {
                throw new Exception('Brak uprawnień!');
            }

            $user->parameters['playerId'] = $dataIn['playerId'];
            $user->parameters['accessKey'] = $dataIn['accessKey'];

            $mMapFields = new Application_Model_MapFields($dataIn['mapId'], $this->_db);
            $fields = new Cli_Model_Fields($mMapFields->getMapFields());

            $token = array(
                'fields' => $fields->toArray()
            );

            $this->sendToUser($user, $token);
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

            case 'castleAdd':
                $mCastle = new Application_Model_Castle($this->_db);
                $mMapCastles = new Application_Model_MapCastles($dataIn['mapId'], $this->_db);
                $mapCastlesIds = $mMapCastles->getMapCastlesIds();
                $castleId = $mCastle->getNextFreeCastleId($mapCastlesIds);
                $mMapCastles->add($dataIn['x'], $dataIn['y'], $castleId);
                break;

            case 'castleRemove':
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
