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
                'description' => ''
            ),
            array(
                'goal' => 'Select army',
                'description' => ''
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

    public function __construct($color, $playerId)
    {
        parent::__construct($color, $playerId);

        $mTutorial = new Application_Model_Tutorial($this->_id);
        $tutorial = $mTutorial->get();

        if (isset($tutorial['tutorialId'])) {
            $this->_tutorialNumber = $tutorial['tutorialNumber'];
            $this->_steps = $this->_allSteps[$this->_tutorialNumber];
        } else {

        }
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['steps'] = $this->_steps;
        return $array;
    }
}

