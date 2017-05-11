<?php
require APPLICATION_PATH . '/../vendor/autoload.php';

use PayPal\Api\PaymentExecution;

use PayPal\Api\Payment;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PaypalController extends Coret_Controller_Authorized
{
    public function indexAction()
    {
        $this->_helper->layout->setLayout('paypal');

        $version = Zend_Registry::get('config')->version;

        $this->view->headLink()->prependStylesheet('/css/main.css?v=' . $version);

        $this->view->jquery();
        $this->view->headScript()->appendFile('/js/default.js?v=' . $version);
        $this->view->headScript()->appendFile('/js/libs.js?v=' . $version);

        $paymentId = $this->_request->getParam('paymentId');
        $PayerID = $this->_request->getParam('PayerID');

        $playerId = $this->_auth->getIdentity()->playerId;

        if ($paymentId && $PayerID) {

            $apiContext = Coret_Model_PayPalApiContext::get();

            $payment = Payment::get($paymentId, $apiContext);

            $execution = new PaymentExecution();
            $execution->setPayerId($PayerID);

            try {
                $payment->execute($execution, $apiContext);

                try {
                    $payment = Payment::get($paymentId, $apiContext);
                } catch (Exception $ex) {
                    $this->view->result = $this->view->translate('Transaction ended with error') . ': PP01';
                    return;
                }
            } catch (Exception $ex) {
                $this->view->result = $this->view->translate('Transaction ended with error') . ': PP02';
                return;
            }

            $mPayPal = new Application_Model_PayPal();
            if ($mPayPal->checkPayment($paymentId, $playerId)) {
                $mTournamentPlayers = new Application_Model_TournamentPlayers();
                $mTournamentPlayers->updateStage($this->_request->getParam('id'), $playerId, 1);

                $this->view->result = $this->view->translate('We got your Payment');
            } else {
                $this->view->result = $this->view->translate('Transaction ended with error') . ': PP03';
            }
        } else {
            $mTournamentPlayers = new Application_Model_TournamentPlayers();
            $mTournamentPlayers->removePlayer($this->_request->getParam('id'), $playerId);
            $this->view->result = $this->view->translate('User Cancelled the Approval');
        }
    }
}
