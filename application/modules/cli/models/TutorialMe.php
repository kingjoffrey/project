<?php

class Cli_Model_TutorialMe extends Cli_Model_Me
{
    private $_number;
    private $_step;

    public function getNumber()
    {
        return $this->_number;
    }

    public function increaseNumber($db)
    {
        $mTutorial = new Application_Model_Tutorial($this->_id, $db);
        $mTutorial->updateNumber($this->_number, $this->_number + 1);
        $this->_number++;
    }

    public function getStep()
    {
        return $this->_step;
    }

    public function increaseStep($db)
    {
        $mTutorial = new Application_Model_Tutorial($this->_id, $db);
        $mTutorial->updateStep($this->_step++, $this->_number);
    }

    public function setStep($step, $db)
    {
        $mTutorial = new Application_Model_Tutorial($this->_id, $db);
        $mTutorial->updateStep($step, $this->_number);
        $this->_step = $step;
    }

    public function initTutorial(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mTutorial = new Application_Model_Tutorial($this->_id, $db);
        $tutorial = $mTutorial->get();

        if (isset($tutorial['tutorialId'])) {
            $this->_number = $tutorial['tutorialNumber'];
            $this->_step = $tutorial['step'];
        } else {
            $this->_number = 0;
            $this->_step = 0;
            $mTutorial->add();
        }
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['tutorial'] = array(
            'number' => $this->_number,
            'step' => $this->_step
        );
        return $array;
    }
}

