<?php

class FriendsController extends Game_Controller_Gui
{
    public function indexAction()
    {
        $mFriend = new Application_Model_Friends();
        $this->view->friends = $mFriend->getFriends($this->_request->getParam('page'), Zend_Auth::getInstance()->getIdentity()->playerId);
        $this->view->searchForm = new Application_Form_Search();
        if ($this->_request->isPost()) {
            if ($this->view->searchForm->isValid($this->_request->getPost())) {
                $mPlayer = new Application_Model_Player();
                $this->view->searchResults = $mPlayer->search($this->view->searchForm->getValue('search'));
            }
        }
    }

    public function addAction()
    {
        if ($this->_request->getParam('friendId')) {
            $mFriend = new Application_Model_Friends();
            $mFriend->create(Zend_Auth::getInstance()->getIdentity()->playerId, $this->_request->getParam('friendId'));
        }
        $this->redirect('/' . Zend_Registry::get('lang') . '/friends');
    }
}

