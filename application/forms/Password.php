<?php

class Application_Form_Password extends Zend_Form
{

    public function init()
    {
        $this->getView();

        $f = new Coret_Form_Password(
            array(
                'label' => $this->getView()->translate('Password'),
                'required' => true,
            )
        );
        $this->addElements($f->getElements());

        $f = new Coret_Form_Password(
            array(
                'label' => $this->getView()->translate('Repeat password'),
                'name' => 'repeatPassword',
                'required' => true,
                'validators' => array(
                    array('identical', false, array('token' => 'password'))
                )
            )
        );
        $this->addElements($f->getElements());

        $f = new Coret_Form_Submit(array('label' => $this->_view->translate('Submit')));
        $this->addElements($f->getElements());
    }
}

