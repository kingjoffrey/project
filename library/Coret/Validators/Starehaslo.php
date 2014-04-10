<?php

class Coret_Validators_Starehaslo extends Zend_Validate_Abstract {

    const STAREHASLO = 'starehaslo';

    protected $_messageTemplates = array(
        self::STAREHASLO => "Podane hasło nie jest poprawne"
    );

    public function isValid($starehaslo) {
        $this->_setValue($starehaslo);

        $validator = new Zend_Validate_Db_RecordExists(
                        array(
                            'table' => 'users',
                            'field' => 'password'
                        )
        );

        if ($validator->isValid(md5($starehaslo)))
            return true;
        else {
            $this->_error('starehaslo');
            return false;
        }
    }

}