<?php

class Zend_View_Helper_Title extends Zend_View_Helper_Abstract
{

    public function title()
    {
        $this->view->placeholder('title')->append(Zend_Registry::get('config')->appName);
    }

}
