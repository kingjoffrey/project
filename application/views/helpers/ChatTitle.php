<?php

class zend_View_Helper_ChatTitle extends Zend_View_Helper_Abstract
{
    public function chatTitle()
    {
            $this->view->placeholder('chatTitle')->append($this->view->translate('Chat'));
    }

}
