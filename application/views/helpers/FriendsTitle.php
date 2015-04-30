<?php

class zend_View_Helper_FriendsTitle extends Zend_View_Helper_Abstract
{
    public function friendsTitle()
    {
            $this->view->placeholder('friendsTitle')->append($this->view->translate('Friends'));
    }

}
