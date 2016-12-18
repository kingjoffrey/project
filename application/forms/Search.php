<?php

class Application_Form_Search extends Zend_Form
{

    public function init()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

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
                'label' => $adapter->translate('Search')
            )
        );
        $this->addElements($f->getElements());
    }
}
