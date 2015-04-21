<?php

class Application_Form_Email extends Zend_Form
{

    public function init()
    {
        $this->setAttrib('id', 'email');

        $f = new Coret_Form_Email(
            array(
                'name' => 'E-mail'
            )
        );
        $this->addElements($f->getElements());

         $this->getView();
        $f = new Coret_Form_Submit(array('label' => $this->_view->translate('Submit')));
        $this->addElements($f->getElements());
    }

}

