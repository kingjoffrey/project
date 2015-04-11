<?php

class FriendsController extends Game_Controller_Gui
{
    public function indexAction()
    {
        $mFriend = new Application_Model_Friends();
        $this->view->friends = $mFriend->getFriends($this->_request->getParam('page'), Zend_Auth::getInstance()->getIdentity()->playerId);
        $this->view->search = new Application_Form_Search();
    }
}

