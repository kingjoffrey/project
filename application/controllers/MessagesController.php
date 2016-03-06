<?php

class MessagesController extends Game_Controller_Gui
{
    public function indexAction()
    {
        $mPrivateChat = new Application_Model_PrivateChat($this->_playerId);
        $this->view->threads = $mPrivateChat->getChatHistoryThreads($this->_request->getParam('page'));
    }

    public function threadAction()
    {
        $playerId = $this->_request->getParam('id');
        if (!$playerId) {
            $this->redirect('/' . $this->_request->getParam('lang') . '/messages');
            return;
        }

        $mPrivateChat = new Application_Model_PrivateChat($this->_playerId);
        $this->view->paginator = $mPrivateChat->getChatHistoryMessages($playerId, $this->_request->getParam('page'));
        $messages = array();
        foreach ($this->view->paginator as $row) {
            $messages[] = $row;
        }
//        $this->view->messages = array_reverse($messages);
        $this->view->messages = $messages;
    }
}
