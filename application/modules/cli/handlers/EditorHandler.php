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

    public function onMessage(WebSocketTransportInterface $user, WebSocketMessageInterface $msg)
    {
        $dataIn = Zend_Json::decode($msg->getData());
        if (Zend_Registry::get('config')->debug) {
            print_r('ZAPYTANIE ');
            print_r($dataIn);
        }

        $db = Cli_Model_Database::getDb();

        if ($dataIn['type'] == 'open') {
            if (!isset($dataIn['playerId'])) {
                $this->sendError($user, 'Brak "playerId"');
                return;
            }

            $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

            if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
                throw new Exception('Brak uprawnień!');
            }

            $user->parameters['playerId'] = $dataIn['playerId'];
            $user->parameters['accessKey'] = $dataIn['accessKey'];

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
                $data = base64_decode($map);
                $file = APPLICATION_PATH . '/../public/img/maps/' . $dataIn['mapId'] . '.png';
                $success = file_put_contents($file, $data);
                break;

            case 'castleAdd':
                $mCastle = new Application_Model_Castle($db);
                $mMapCastles = new Application_Model_MapCastles($dataIn['mapId'], $db);
                $mapCastlesIds = $mMapCastles->getMapCastlesIds();
                $castleId = $mCastle->getNextFreeCastleId($mapCastlesIds);
                $mMapCastles->add($dataIn['x'], $dataIn['y'], $castleId);
                break;

            case 'castleRemove':
                $mMapCastles = new Application_Model_MapCastles($dataIn['mapId'], $db);
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

        $token = array(
            'type' => 'close',
            'id' => $user->parameters['playerId']
        );

        foreach ($this->getFriends($user->parameters['playerId']) AS $friend) {
            foreach ($this->getUsers() as $u) {
                if ($friend['friendId'] == $u->parameters['playerId']) {
                    $this->sendToUser($u, $token);
                }
            }
        }

        $this->removeFriends($user->parameters['playerId']);
        unset($this->_users[$user->parameters['playerId']]);
    }
}
