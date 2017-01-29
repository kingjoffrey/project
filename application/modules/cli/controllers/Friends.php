<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class FriendsController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $db = $handler->getDb();

        $mFriend = new Application_Model_Friends($db);
        $friends = $mFriend->getFriends($user->parameters['playerId']);

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

    function delete(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn){

    }
}