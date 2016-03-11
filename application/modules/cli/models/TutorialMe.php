<?php

class Cli_Model_TutorialMe extends Cli_Model_Me
{
    private $_tutorialNumber;
    private $_allSteps = array(
        array(
            array(
                'goal' => 'Set production',
                'description' => 'To lead the conquest you need to have an army. To have an army you need to produce units in castles.'
            ),
            array(
                'goal' => 'Change turn',
                'description' => 'When you have done everything (eg.: your armies have no moves left) you are ready to end your turn. Click on wheel in the right-top corner to change turn.'
            ),
            array(
                'goal' => 'Move army',
                'description' => 'This is your new turn. You should have new unit in your castle. Click on the army to select it. Now when you move your mouse you should see green wheels on the ground. They show the path that will be followed by the army. Move your army by clicking on the ground.'
            ),
            array(
                'goal' => 'Conquer "Shadow" castle',
                'description' => ''
            ),
            array(
                'goal' => 'Set unit relocation',
                'description' => ''
            ),
            array(
                'goal' => 'Conquer enemy castle',
                'description' => ''
            ),
        ),
        array(
            array(
                'goal' => 'Make ship',
                'description' => ''
            ),
            array(
                'goal' => 'Load hero on ship',
                'description' => ''
            ),
            array(
                'goal' => 'Swim to shore near ruins',
                'description' => ''
            ),
            array(
                'goal' => 'Unload hero on shore',
                'description' => ''
            ),
            array(
                'goal' => 'Take hero to ruins',
                'description' => ''
            ),
            array(
                'goal' => 'Search ruins',
                'description' => ''
            ),
            array(
                'goal' => 'Conquer all castles',
                'description' => ''
            ),
        ),
        array(
            array(
                'goal' => 'Improve castle defense to 4',
                'description' => 'Your castle does not provide sufficient protection. You have to Improve castle defense to maximum.'
            ),
            array(
                'goal' => 'Take over all towers',
                'description' => ''
            ),
            array(
                'goal' => 'Win',
                'description' => ''
            ),
        ),
    );
    private $_steps;
    private $_step;

    public function getTutorialNumber()
    {
        return $this->_tutorialNumber;
    }

    public function setTutorialNumber($oldTutorialNumber, $newTutorialNumber, $playerId, $db)
    {
        $mTutorial = new Application_Model_Tutorial($playerId, $db);
        $mTutorial->updateTutorialNumber($oldTutorialNumber, $newTutorialNumber);
        $this->_tutorialNumber = $newTutorialNumber;
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
            $this->_tutorialNumber = $tutorial['tutorialNumber'];
            $this->_step = $tutorial['step'];
        } else {
            $this->_tutorialNumber = 0;
            $this->_step = 0;
            $mTutorial->init();
        }
        $this->_steps = $this->_allSteps[$this->_tutorialNumber];
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['tutorial'] = array(
            'tutorialNumber' => $this->_tutorialNumber,
            'steps' => $this->_steps,
            'step' => $this->_step
        );
        return $array;
    }
}

