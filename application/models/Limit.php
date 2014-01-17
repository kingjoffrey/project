<?php

class Application_Model_Limit
{

    static public function timeLimits()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();
        return array(
            0 => $adapter->translate('no limit'),
            1 => '10 ' . $adapter->translate('minutes'),
            2 => '20 ' . $adapter->translate('minutes'),
            3 => '30 ' . $adapter->translate('minutes'),
            4 => '40 ' . $adapter->translate('minutes'),
            5 => '50 ' . $adapter->translate('minutes'),
            6 => '1 ' . $adapter->translate('hour'),
            12 => $adapter->translate('2 hours'),
            18 => $adapter->translate('3 hours'),
            24 => $adapter->translate('4 hours'),
            30 => $adapter->translate('5 hours'),
            36 => $adapter->translate('6 hours'),
            42 => $adapter->translate('7 hours'),
            48 => $adapter->translate('8 hours'),
        );
    }

    static public function turnTimeLimit()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $adapter = $translator->getAdapter();
        return array(
            0 => $adapter->translate('no limit'),
            1 => $adapter->translate('1 minute'),
            2 => $adapter->translate('2 minutes'),
            3 => $adapter->translate('3 minutes'),
            5 => $adapter->translate('5 minutes'),
            10 => '10 ' . $adapter->translate('minutes'),
            20 => '20 ' . $adapter->translate('minutes'),
            30 => '30 ' . $adapter->translate('minutes'),
            60 => '1 ' . $adapter->translate('hour'),
            120 => $adapter->translate('2 hours'),
            180 => $adapter->translate('3 hours'),
            1440 => $adapter->translate('1 day')
        );
    }
}
