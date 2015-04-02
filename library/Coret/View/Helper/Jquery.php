<?php

class Zend_View_Helper_Jquery extends Zend_View_Helper_Abstract {

    public function jquery() {
        $this->view->headScript()->prependFile('/js/jquery-2.1.3.js');
    }

}