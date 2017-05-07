<?php

class Application_Form_Registration extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');

        $this->addElement('text', 'login', array(
                'label' => $this->getView()->translate('Login'),
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('StringLength', false, array(3, 32)),
                    new Zend_Validate_Db_NoRecordExists(
                        array(
                            'table' => 'player',
                            'field' => 'login'
                        )
                    ))

            )
        );

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

        $this->addElement('submit', 'submit', array('label' => $this->getView()->translate('Register')));
    }

}

