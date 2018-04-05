<?php

class Application_Form_Password extends Zend_Form
{

    public function init()
    {
        $this->setAttrib('id', 'passwordForm');

        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

        $f = new Coret_Form_Password(
            array(
                'label' => $adapter->translate('Password'),
                'required' => true,
            )
        );
        $this->addElements($f->getElements());

        $f = new Coret_Form_Password(
            array(
                'label' => $adapter->translate('Repeat password'),
                'name' => 'repeatPassword',
                'required' => true,
                'validators' => array(
                    array('identical', false, array('token' => 'password'))
                )
            )
        );
        $this->addElements($f->getElements());

        $f = new Coret_Form_Submit(array('label' => $adapter->translate('Submit'), 'id' => 'submit3'));
        $this->addElements($f->getElements());
    }
}

