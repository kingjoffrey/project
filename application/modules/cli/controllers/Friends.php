<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class FriendsController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $db = $handler->getDb();

        $mFriends = new Application_Model_Friends($db);
        $this->friends($mFriends, $user, $handler);
    }

    function add(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if ($dataIn['id']) {
            $db = $handler->getDb();
            $mFriends = new Application_Model_Friends($db);
            if ($mFriends->create($user->parameters['playerId'], $dataIn['id'])) {
                $this->friends($mFriends, $user, $handler);
            }
        }
    }

    function delete(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $db = $handler->getDb();
        $mFriends = new Application_Model_Friends($db);
        if ($mFriends->remove($user->parameters['playerId'], $dataIn['id'])) {
            $token = array(
                'type' => 'friends',
                'action' => 'delete',
                'id' => $dataIn['id']
            );
            $handler->sendToUser($user, $token);
        }
    }

    private function friends($mFriends, WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $friends = $mFriends->getFriends($user->parameters['playerId']);

        $friendsFormatted = array();
        foreach ($friends as $row) {
            $friendsFormatted[$row['friendId']] = $row['firstName'] . ' ' . $row['lastName'];
        }

        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'friends',
            'action' => 'index',
            'data' => $view->render('friends/index.phtml'),
            'friends' => $friendsFormatted,
        );
        $handler->sendToUser($user, $token);

    }
}