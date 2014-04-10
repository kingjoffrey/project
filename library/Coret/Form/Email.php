<?php

class Coret_Form_Email extends Zend_Form
{

    public function init()
    {
        if (isset($this->_attribs['validators']) && $this->_attribs['validators']) {
            $this->_attribs['validators'][] = array('EmailAddress', false);
        } else {
            $this->_attribs['validators'] = array(array('EmailAddress', false));
        }

        $f = new Coret_Form_Varchar($this->_attribs);
        $this->addElements($f->getElements());
    }

}

