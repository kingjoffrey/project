<?php

class zend_View_Helper_Friends extends Zend_View_Helper_Abstract
{
    public function friends()
    {
        $mFriend = new Application_Model_Friends();
        $friends = $mFriend->getFriends(Zend_Controller_Front::getInstance()
            ->getRequest()->getParam('page'), Zend_Auth::getInstance()->getIdentity()->playerId);

//        $this->view->placeholder('friends')
//            ->setSeparator('</div><div>')
//            ->setPrefix('<div>')
//            ->setPostfix('</div>');

        foreach ($friends as $row) {
            $this->view->placeholder('friends')->append('<div id="' . $row['friendId'] . '" class="friends"><div id="online"></div><span>' . $row['firstName'] . ' ' . $row['lastName'] . '</span><div id="trash"></div></div>');
        }
    }

}
