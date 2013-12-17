<?php

class Application_Model_DrawHill extends Application_Model_Draw
{
    private $_min = 60;
    private $_max = 90;

    protected function setInnerColors($x, $y)
    {
        $rand = rand($this->_min, $this->_max);

        $this->_colors['r'][$x][$y] = $rand;
        $this->_colors['g'][$x][$y] = $rand;
        $this->_colors['b'][$x][$y] = $rand;
    }

    protected function setBorderColors($x, $y)
    {
        $this->setInnerColors($x, $y);
    }
}