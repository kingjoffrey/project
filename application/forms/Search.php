<?php

class Application_Form_Search extends Zend_Form
{

    public function init()
    {
        $this->setAttrib('id', 'search');

        $f = new Coret_Form_Varchar(
            array(
                'name' => 'search'
            )
        );
        $this->addElements($f->getElements());

        $f = new Coret_Form_Submit(
            array(
                'name' => 'submit',
                'label' => $this->getView()->translate('Search')
            )
        );
        $this->addElements($f->getElements());
    }
}
