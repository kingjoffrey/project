<?php

class Application_Form_Login extends Zend_Form
{

    public function init()
    {
        $this->setAttrib('id', 'login');

        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

        $f = new Coret_Form_Varchar(
            array(
                'label' => $adapter->translate('Login'),
                'name' => 'login'
            )
        );
        $this->addElements($f->getElements());

        $f = new Coret_Form_Submit(array('label' => $adapter->translate('Submit')));
        $this->addElements($f->getElements());
    }

}

