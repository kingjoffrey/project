<?php

class Zend_View_Helper_Logout extends Zend_View_Helper_Abstract
{

    public function Logout()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $this->view->placeholder('logout')->append('<a href="/' . Zend_Registry::get('lang') . '/login/logout" id="logout" class="">' . $this->view->translate('Logout') . ' (' . $identity->firstName . ' ' . $identity->lastName . ')</a>');
    }

}
