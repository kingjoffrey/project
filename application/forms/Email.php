<?php

class Application_Form_Email extends Zend_Form
{

    public function init()
    {
        $this->setAttrib('id', 'email');

        $this->getView();

        $f = new Coret_Form_Email(
            array(
                'label' => $this->_view->translate('E-mail'),
                'name' => 'login'
            )
        );
        $this->addElements($f->getElements());

        $f = new Coret_Form_Submit(array('label' => $this->_view->translate('Submit')));
        $this->addElements($f->getElements());
    }

}

