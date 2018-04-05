<?php

class Application_Form_Player extends Zend_Form
{
    public function init()
    {
        $this->setAttrib('id', 'playerForm');

        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

        $f = new Coret_Form_Varchar(
            array(
                'label' => $adapter->translate('First name') . ' (' . $adapter->translate('imaginary') . ')',
                'name' => 'firstName',
                'required' => true,
            )
        );
        $this->addElements($f->getElements());
        $f = new Coret_Form_Varchar(
            array(
                'label' => $adapter->translate('Last name') . ' (' . $adapter->translate('imaginary') . ')',
                'name' => 'lastName',
                'required' => true,
            )
        );
        $this->addElements($f->getElements());
        $f = new Coret_Form_Submit(array('label' => $adapter->translate('Submit'), 'id' => 'submit1'));
        $this->addElements($f->getElements());
    }
}

