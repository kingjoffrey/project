<?php

class zend_View_Helper_ChatInput extends Zend_View_Helper_Abstract
{
    public function chatInput()
    {
            $this->view->placeholder('chatInput')->append($this->view->translate('Select friend from friends list'));
    }

}
