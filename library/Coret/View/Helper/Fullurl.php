<?php

class Coret_View_Helper_Fullurl extends Zend_View_Helper_Abstract {

    static public function fullUrl($url) {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $url = $request->getScheme() . '://' . $request->getHttpHost() . $url;
        return $url;
    }

}
