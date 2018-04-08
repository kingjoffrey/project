<?php

class Application_Form_Login extends Zend_Form
{
    private $_db;

    public function __construct($db)
    {
        $this->_db = $db;
        parent::__construct();
    }

    public function init()
    {
        $this->setAttrib('id', 'loginForm');

        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();

        $f = new Coret_Form_Varchar(
            array(
                'label' => $adapter->translate('Login'),
                'name' => 'login',
                'required' => true,
                'validators' => array(
                    array('Db_NoRecordExists', true, array(
                        'table' => 'player',
                        'field' => 'login',
                        'messages' => array(
                            'recordFound' => $adapter->translate('This login is already registered')
                        ),
                        'adapter' => $this->_db
                    ))
                )
            )
        );

        $this->addElements($f->getElements());

        $f = new Coret_Form_Submit(array('label' => $adapter->translate('Submit'), 'id' => 'submit2'));
        $this->addElements($f->getElements());
    }

}

