<?php

class Coret_Validators_Url extends Zend_Validate_Abstract
{

    const URL = 'url';

    protected $_messageTemplates = array(
        self::URL => "Podany adres nie jest poprawnym URL"
    );

    public function isValid($url)
    {
//        $validator = new Zend_Validate_Alnum();
//        if (!$validator->isValid($url)) {
//            $this->_error('url');
//            return false;
//        }

        if (Zend_Uri::check($url))
            return true;
        else {
            $this->_error('url');
            return false;
        }
    }

}