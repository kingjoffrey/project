<?php
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class Coret_Model_PayPalApiContext extends Coret_Db_Table_Abstract
{
    static public function get($logType = 'cli')
    {
        $payPalConfig = Zend_Registry::get('config')->paypal;

        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $payPalConfig->clientId,
                $payPalConfig->clientSecret
            )
        );

        $apiContext->setConfig(
            array(
                'mode' => $payPalConfig->mode,
                'log.LogEnabled' => true,
                'log.FileName' => APPLICATION_PATH . '/../log/' . date('Ymd') . '_PayPal_' . $logType . '.log',
                'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                'cache.enabled' => false,
            )
        );

        return $apiContext;
    }
}
