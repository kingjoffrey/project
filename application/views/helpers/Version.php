<?php

class Zend_View_Helper_Version extends Zend_View_Helper_Abstract {

    public function Version() {
        $this->view->placeholder('version')->append('<div id="versionNumber"><span>' . Zend_Registry::get('config')->version . '</span></div>');
    }

}
