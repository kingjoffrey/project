<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class Cli_Model_MainOpen
{

    public function __construct($dataIn, WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        if (!isset($dataIn['playerId'])) {
            echo('Cli_Model_MainOpen: Brak "playerId"');
            return;
        }
        if (!isset($dataIn['langId'])) {
            echo('Cli_Model_MainOpen: Brak "langId"');
            return;
        }

        $db = $handler->getDb();
        $mWebSocket = new Application_Model_Websocket($dataIn['playerId'], $db);

        if (!$mWebSocket->checkAccessKey($dataIn['accessKey'], $db)) {
            echo ('Brak uprawnień (playerId=' . $dataIn['playerId'] . ')') . "\n";
            return;
        }

        new Cli_Model_Language($dataIn['langId'], $db);

        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

        $user->parameters['playerId'] = $dataIn['playerId'];
        $user->parameters['accessKey'] = $dataIn['accessKey'];

        $token = array(
            'type' => 'open',
            'menu' => array(
                'play' => $adapter->translate('Play'),
                'tournament' => $adapter->translate('Tournament'),
                'load' => $adapter->translate('Load game'),
                'halloffame' => $adapter->translate('Hall of Fame'),
                'players' => $adapter->translate('Players'),
                'friends' => $adapter->translate('Friends'),
                'profile' => $adapter->translate('Profile'),
                'contact' => $adapter->translate('Contact'),
                'help' => $adapter->translate('Help'),
                'editor' => $adapter->translate('Map editor'),
            ),
            'env' => APPLICATION_ENV
        );

        $handler->sendToUser($user, $token);
    }
}
