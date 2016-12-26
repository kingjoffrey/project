<?php

class MessagesController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();
        $db = $handler->getDb();

        if (!isset($dataIn['page'])) {
            $dataIn['page'] = 1;
        }

        $mPrivateChat = new Application_Model_PrivateChat($user->parameters['playerId'], $db);

        $paginator = $mPrivateChat->getChatHistoryThreads($dataIn['page']);

        $ids = '';
        $recipients = array();
        foreach ($paginator as $row) {
            if ($ids) {
                $ids .= ',';
            }
            if ($row['playerId'] == $user->parameters['playerId']) {
                $ids .= $row['recipientId'];
            } else {
                $recipients[$row['playerId']] = $row;
                $ids .= $row['playerId'];
            }
        }

        if ($ids) {
            $mPlayer = new Application_Model_Player($db);
            $players = $mPlayer->getPlayersNames($ids);
            foreach ($paginator as &$row) {
                if ($row['playerId'] == $user->parameters['playerId']) {
                    $row['name'] = $players[$row['recipientId']];
                    $row['id'] = $row['recipientId'];
                    if (isset($recipients[$row['recipientId']])) {
                        $row['messages'] += $recipients[$row['recipientId']]['messages'];
                        $row['read'] = $recipients[$row['recipientId']]['read'];
                    } else {
                        $row['read'] = 0;
                    }
                } else {
                    if (isset($recipients[$row['playerId']])) {
                        continue;
                    }
                    $row['name'] = $players[$row['playerId']];
                    $row['id'] = $row['playerId'];
                }
            }
        }

        $view->threads = $paginator;

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'messages',
            'action' => 'index',
            'data' => $view->render('messages/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }

    public function thread(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $playerId = $this->_request->getParam('id');
        if (!$playerId) {
            $this->redirect('/messages');
            return;
        }

        $view = new Zend_View();

        $mPrivateChat = new Application_Model_PrivateChat($user->parameters['playerId']);
        $view->paginator = $mPrivateChat->getChatHistoryMessages($playerId, $dataIn['page']);
        $messages = array();
        foreach ($view->paginator as $row) {
            $messages[] = $row;
        }
//        $this->view->messages = array_reverse($messages);
        $view->messages = $messages;

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'messages',
            'action' => 'index',
            'data' => $view->render('messages/index.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}