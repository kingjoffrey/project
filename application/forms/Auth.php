<?php

class Application_Form_Auth extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');

        $this->addElement('text', 'login', array(
            'label' => $this->getView()->translate('Login'),
            'required' => true,
            'filters' => array('StringTrim')
        ));
        $this->addElement('password', 'password', array(
            'label' => $this->getView()->translate('Password'),
            'required' => true,
            'filters' => array('StringTrim')
        ));

        $f = new Coret_Form_Checkbox(array('name' => 'rememberMe', 'label' => $this->getView()->translate('Remember me'), 'value' => 1));
        $this->addElements($f->getElements());

        $this->addElement('submit', 'submit', array('label' => $this->getView()->translate('Sign in')));
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }

}

