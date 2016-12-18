<?php

class Application_Form_Email extends Zend_Form
{

    public function init()
    {
        $this->setAttrib('id', 'email');

        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

        $f = new Coret_Form_Email(
            array(
                'label' => $adapter->translate('E-mail'),
                'name' => 'login'
            )
        );
        $this->addElements($f->getElements());

        $f = new Coret_Form_Submit(array('label' => $adapter->translate('Submit')));
        $this->addElements($f->getElements());
    }

}

