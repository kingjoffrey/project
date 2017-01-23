<?php

class Application_Form_Registration extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');

        $f = new Coret_Form_Varchar(
            array(
                'label' => $this->getView()->translate('First name') . ' (' . $this->getView()->translate('imaginary') . ')',
                'name' => 'firstName',
                'required' => true,
            )
        );
        $this->addElements($f->getElements());
        $f = new Coret_Form_Varchar(
            array(
                'label' => $this->getView()->translate('Last name') . ' (' . $this->getView()->translate('imaginary') . ')',
                'name' => 'lastName',
                'required' => true,
            )
        );
        $this->addElements($f->getElements());

        $this->addElement('text', 'login', array(
                'label' => $this->getView()->translate('Email'),
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

