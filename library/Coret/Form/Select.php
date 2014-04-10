<?php

class Coret_Form_Select extends Zend_Form
{

    public function init()
    {
        $name = $this->_attribs['name'];
        if (isset($this->_attribs['label'])) {
            $label = $this->_attribs['label'];
        } else {
            $label = '';
        }

        $options = $this->_attribs['opt'];

        if (isset($this->_attribs['required']) && $this->_attribs['required']) {
            $label .= '*';
            $required = $this->_attribs['required'];
        } else {
            $required = false;
        }

        if (isset($this->_attribs['validators']) && $this->_attribs['validators']) {
            $validators = $this->_attribs['validators'];
            $validators[] = array(
                new Zend_Validate_InArray(
                    array('haystack' => array_keys($options))
                ),
                'presence' => 'required',
                'messages' => array(
                    "'%value%' is not a valid.",
                )
            );
        } else {
            $validators = array(array(
                new Zend_Validate_InArray(
                    array('haystack' => array_keys($options))
                ),
                'presence' => 'required',
                'messages' => array(
                    "'%value%' is not a valid.",
                )
            ));
        }

        if (isset($this->_attribs['value'])) {
            $value = $this->_attribs['value'];
        } else {
            $value = null;
        }

        $this->addElement('select', $name, array(
                'label' => $label,
                'required' => $required,
                'multiOptions' => $options,
                'value' => $value,
                'validators' => $validators,
            )
        );
    }

}

