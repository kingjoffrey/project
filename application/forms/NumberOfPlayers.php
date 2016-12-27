<?php

class Application_Form_NumberOfPlayers extends Zend_Form
{

    public function init()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

        $f = new Coret_Form_Varchar(
            array(
                'name' => 'x',
                'label' => $adapter->translate('Number of players') . ':',
                'value' => $this->_attribs['numberOfPlayers'],
                'attr' => array('disabled' => 'disabled')
            )
        );
        $this->addElements($f->getElements());

        $f = new Coret_Form_Hidden(
            array(
                'name' => 'numberOfPlayers',
                'value' => $this->_attribs['numberOfPlayers'],
                'validators' => array(array('Alnum'), array('identical', false, array(array('token' => $this->_attribs['numberOfPlayers'], 'strict' => FALSE)))),
            )
        );
        $this->addElements($f->getElements());
    }

}

