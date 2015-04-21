<?php

class Application_Form_Player extends Zend_Form
{
    public function init()
    {
        $this->getView();
        $f = new Coret_Form_Varchar(
            array(
                'label' => $this->_view->translate('First name') . ' (' . $this->_view->translate('imaginary') . ')',
                'name' => 'firstName',
                'required' => true,
            )
        );
        $this->addElements($f->getElements());
        $f = new Coret_Form_Varchar(
            array(
                'label' => $this->_view->translate('Last name') . ' (' . $this->_view->translate('imaginary') . ')',
                'name' => 'lastName',
                'required' => true,
            )
        );
        $this->addElements($f->getElements());
        $f = new Coret_Form_Submit(array('label' => $this->_view->translate('Submit')));
        $this->addElements($f->getElements());
    }
}

