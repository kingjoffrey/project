<?php

class Cli_Model_TutorialMe extends Cli_Model_Me
{
    private $_number;
    private $_step;

    public function getNumber()
    {
        return $this->_number;
    }

    public function setNumber($oldTutorialNumber, $newTutorialNumber, $playerId, $db)
    {
        $mTutorial = new Application_Model_Tutorial($playerId, $db);
        $mTutorial->updateTutorialNumber($oldTutorialNumber, $newTutorialNumber);
        $this->_number = $newTutorialNumber;
    }

    public function getStep()
    {
        return $this->_step;
    }

    public function setStep($step, $tutorialNumber, $playerId, $db)
    {
        $mTutorial = new Application_Model_Tutorial($playerId, $db);
        $mTutorial->updateStep($step, $tutorialNumber);
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
            $mTutorial->init();
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

