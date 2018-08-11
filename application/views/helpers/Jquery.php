<?php

class Zend_View_Helper_Jquery extends Zend_View_Helper_Abstract
{

    public function jquery()
    {
        $this->view->headScript()->prependFile('/js/' . Zend_Registry::get('config')->version . 'jquery-3.1.1.min.js');
    }

}