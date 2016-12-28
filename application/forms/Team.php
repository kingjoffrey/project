<?php

class Application_Form_Team extends Zend_Form
{

    public function init()
    {
        $f = new Coret_Form_Select(array('name' => 'mapPlayerId', 'label' => null, 'opt' => $this->_attribs['longNames']));
        $this->addElements($f->getElements());
    }

}

