<?php

class MessagesController extends Game_Controller_Gui
{
    public function indexAction()
    {
        $mPrivateChat = new Application_Model_PrivateChat($this->_playerId);
        $this->view->messages = $mPrivateChat->getChatHistory($this->_request->getParam('page'));
    }
}
