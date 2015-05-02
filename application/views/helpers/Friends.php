<?php

class zend_View_Helper_Friends extends Zend_View_Helper_Abstract
{
    public function friends()
    {
        $mFriend = new Application_Model_Friends();
        $friends = $mFriend->getFriends(Zend_Auth::getInstance()->getIdentity()->playerId);

//        $this->view->placeholder('friends')
//            ->setSeparator('</div><div>')
//            ->setPrefix('<div>')
//            ->setPostfix('</div>');

        if ($friends) {
            foreach ($friends as $row) {
                $this->view->placeholder('friends')->append('<div id="' . $row['friendId'] . '" class="friends"><div id="online"></div><div id="trash"></div><span>' . $row['firstName'] . ' ' . $row['lastName'] . '</span></div>');
            }
        } else {
            $this->view->placeholder('friends')->append($this->view->translate('You don\'t have friends') . ': <a href="/' . Zend_Registry::get('lang') . '/players">' . $this->view->translate('find some friends') . '</a>.');
        }
    }

}
