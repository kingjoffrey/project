<?php

class Cli_Model_Tutorial
{
    private $_tutorial = array();

    public function __construct(Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mTutorial = new Application_Model_Tutorial($db);
        $tutorial = $mTutorial->get();

        foreach ($tutorial as $row) {
            $array = array(
                'goal' => $row['goal'],
                'description' => $row['description']
            );
            $this->_tutorial[$row['number']][$row['step']] = $array;
        }
    }

    public function toArray()
    {
        return $this->_tutorial;
    }
}