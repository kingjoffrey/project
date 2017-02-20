<?php

class Cli_Model_NameGenerator
{
    private $_firstName = array("Kr", "Ca", "Ra", "Mrok", "Cru", "Ray", "Bre", "Zed", "Drak", "Mor", "Jag", "Mer", "Jar", "Mjol", "Zork", "Mad", "Cry", "Zur", "Creo", "Azak", "Azur", "Rei", "Cro", "Mar", "Luk", "a", "e", "i", "o", "u", "ei", "ai", "ou", "j", "ji", "y", "oi", "au", "oo");
    private $_secondName = array("an", "ar", "ad", "et", "ief", "ob", "or", "od");
    private $_heroNames = array("hammer", "stone", "rock", "fist", "strength", "hard");

    private $_firstNameLength;
    private $_secondNameLength;
    private $_heroNamesLength;

    public function __construct()
    {
        $this->_firstNameLength = count($this->_firstName);
        $this->_secondNameLength = count($this->_secondName);
        $this->_heroNamesLength = count($this->_heroNames);
    }

    private function generateHeroFirstName()
    {
        $firstGeneratedNumber = mt_rand(0, $this->_firstNameLength);
        $secondGeneratedNumber = mt_rand(0, $this->_secondNameLength);

        if ($firstGeneratedNumber == $this->_firstNameLength) {
            $firstGeneratedNumber--;
        }

        if ($secondGeneratedNumber == $this->_secondNameLength) {
            $secondGeneratedNumber--;
        }

        $name = ucfirst($this->_firstName[$firstGeneratedNumber] . $this->_secondName[$secondGeneratedNumber]);

        if ($name != "Oo") {
            return $name;
        }
    }

    private function generateHeroSecondName()
    {
        $firstNumber = mt_rand(0, $this->_heroNamesLength);
        $secondNumber = $this->duplicateNumber($firstNumber, $this->_heroNames);
        if ($firstNumber == $this->_heroNamesLength) {
            $firstNumber--;
        } else if ($secondNumber == $this->_heroNamesLength) {
            $secondNumber--;
        }
        $newHeroName = $this->_heroNames[$firstNumber] . $this->_heroNames[$secondNumber];
        $newHeroName = ucfirst($newHeroName);
        if ($newHeroName != "Hardhard") {
            return $newHeroName;
        }
    }

    private function duplicateNumber($firstNumber)
    {
        $name = mt_rand(0, $this->_heroNamesLength);
        if ($name == $firstNumber) {
            return $this->duplicateNumber($firstNumber);
        } else {
            if ($name != $firstNumber)
                return $name;
        }
    }

    public function generateHeroName()
    {
        return $this->generateHeroFirstName() . ' ' . $this->generateHeroSecondName();
    }
}