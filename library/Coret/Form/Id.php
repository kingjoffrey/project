<?php

class Coret_Form_Id extends Zend_Form
{

    public function init()
    {
        if (isset($this->_attribs['value'])) {
            $value = $this->_attribs['value'];
        } else {
            $value = '';
        }

        $this->addElement('hidden', 'id', array(
            'value' => $value,
        ));
    }

}

