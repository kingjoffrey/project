<?php

class Application_Form_Language extends Zend_Form
{

    public function init()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

        $f = new Coret_Form_Select(array('name' => 'lang', 'label' => $adapter->translate('Select language'), 'opt' => $this->_attribs['langList']));
        $this->addElements($f->getElements());
    }

}

