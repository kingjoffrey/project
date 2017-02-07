<?php

class Cli_Model_Language
{

    public function __construct($id_lang, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $mLanguage = new Application_Model_Language($db);
        $countryCode = $mLanguage->getCountryCodeByLanguageId($id_lang);

        $translate = new Zend_Translate(
            'gettext',
            APPLICATION_PATH . "/resources/languages/",
            null,
            array('scan' => Zend_Translate::LOCALE_DIRECTORY)
        );

        if (!$translate->isAvailable($countryCode)) {
            $countryCode = Zend_Registry::get('config')->lang;
            $id_lang = Zend_Registry::get('config')->id_lang;
            if (!$countryCode || !$id_lang) {
                throw new Zend_Exception('Lang not enabled in application.ini');
            }
        }

        $translate->setLocale($countryCode);

        Zend_Registry::set('Zend_Translate', $translate);

        Zend_Registry::set('lang', $countryCode);
        Zend_Registry::set('id_lang', $id_lang);

        Zend_Validate_Abstract::setDefaultTranslator(
            new Zend_Translate(
                'array',
                APPLICATION_PATH . '/resources/languages/' . $countryCode . '/Zend_Validate.php',
                $countryCode,
                array('scan' => Zend_Translate::LOCALE_DIRECTORY)
            )
        );
    }
}