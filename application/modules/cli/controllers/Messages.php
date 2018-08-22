<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class MessagesController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $db = $handler->getDb();

        if (!isset($dataIn['page'])) {
            $dataIn['page'] = 1;
        }

        $mPrivateChat = new Application_Model_PrivateChat($user->parameters['playerId'], $db);

        $paginator = $mPrivateChat->getChatHistoryThreads($dataIn['page']);

        $threads = array();

        $mPlayer = new Application_Model_Player($db);
        $players = $mPlayer->getPlayersNames($ids);
        foreach ($paginator as $row) {

            if ($row['playerId'] == $user->parameters['playerId']) {
                $threads['name'] = $players[$row['recipientId']];
                $threads['id'] = $row['recipientId'];
            } else {
                $threads['name'] = $players[$row['playerId']];
                $threads['id'] = $row['playerId'];
            }
        }

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'messages',
            'action' => 'index',
            'data' => $view->render('messages/index.phtml'),
            'threads' => $threads
        );
        $handler->sendToUser($user, $token);
    }

    public function thread(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $playerId = $dataIn['id'];

        if (!$playerId) {
            return;
        }

        $db = $handler->getDb();

        $mPrivateChat = new Application_Model_PrivateChat($user->parameters['playerId'], $db);
        $chatHistory = $mPrivateChat->getChatHistoryMessages($playerId);

        $messages = array();
        foreach ($chatHistory as $row) {
            $messages[] = array(
                'date' => Coret_View_Helper_Formatuj::date($row['date']),
                'name' => $row['firstName'] . ' ' . $row['lastName'],
                'message' => $row['message']
            );
        }

        $messages = array_reverse($messages);

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'messages',
            'action' => 'thread',
            'data' => $view->render('messages/thread.phtml'),
            'messages' => $messages
        );

        $handler->sendToUser($user, $token);
    }
}