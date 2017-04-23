<?php

class Cli_Model_TutorialProgressMe extends Cli_Model_Me
{
    private $_number = 0;
    private $_step = 0;

    public function getNumber()
    {
        return $this->_number;
    }

    public function increaseNumber($db)
    {
        $mTutorialProgress = new Application_Model_TutorialProgress($this->_id, $db);
        $mTutorialProgress->updateNumber($this->_number, $this->_number + 1);
        $this->_number++;
    }

    public function resetNumber($db)
    {
        $mTutorialProgress = new Application_Model_TutorialProgress($this->_id, $db);
        $mTutorialProgress->updateNumber($this->_number, 0);
        $this->_number = 0;
    }

    public function getStep()
    {
        return $this->_step;
    }

    public function increaseStep($db)
    {
        $this->_step++;
        $mTutorialProgress = new Application_Model_TutorialProgress($this->_id, $db);
        $mTutorialProgress->updateStep($this->_step, $this->_number);
    }

    public function setStep($step, $db)
    {
        $mTutorialProgress = new Application_Model_TutorialProgress($this->_id, $db);
        $mTutorialProgress->updateStep($step, $this->_number);
        $this->_step = $step;
    }

    public function initTutorial(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mTutorialProgress = new Application_Model_TutorialProgress($this->_id, $db);
        $tutorialProgress = $mTutorialProgress->get();
        if ($tutorialProgress) {
            $this->_number = $tutorialProgress['number'];
            $this->_step = $tutorialProgress['step'];
        } else {
            $mTutorialProgress->add();
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

