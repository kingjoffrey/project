<?php

class Application_Form_Hero extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');

        $this->addElement('text', 'name',
            array(
                'label' => 'Name: ',
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('StringLength', false, array(1, 32)),
                    array('Alnum')
                )
            )
        );
        $this->addElement('submit', 'submit', array('label' => 'Submit'));
    }


}

