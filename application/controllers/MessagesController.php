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
            return;
        }

        $mPrivateChat = new Application_Model_PrivateChat($this->_playerId);
        $this->view->messages = $mPrivateChat->getChatHistoryMessages($playerId, $this->_request->getParam('page'));
    }
}
