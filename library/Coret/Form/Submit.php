<?php

class Coret_Form_Submit extends Zend_Form
{

    public function init()
    {
        if (isset($this->_attribs['label'])) {
            $label = $this->_attribs['label'];
        } else {
            $label = '';
        }

        if (!isset($this->_attribs['name'])) {
            $this->_attribs['name'] = 'submit';
        }

        if (isset($this->_attribs['value'])) {
            $value = $this->_attribs['value'];
        } else {
            $value = '';
        }

        if (isset($this->_attribs['class']) && $this->_attribs['class']) {
            $class = $this->_attribs['class'];
        } else {
            $class = '';
        }

        if (isset($this->_attribs['id']) && $this->_attribs['id']) {
            $id = $this->_attribs['id'];
        } else {
            $id = $this->_attribs['name'];
        }

        $this->addElement('submit', $this->_attribs['name'], array(
                'label' => $label,
                'value' => $value,
                'class' => $class,
                'id' => $id,
            )
        );
    }

}

