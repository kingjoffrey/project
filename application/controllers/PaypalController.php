<?php
require APPLICATION_PATH . '/../vendor/autoload.php';

use PayPal\Api\PaymentExecution;

use PayPal\Api\Payment;

class PaypalController extends Coret_Controller_AuthorizedFrontend
{
    public function indexAction()
    {
        $this->_helper->layout->setLayout('paypal');

        $version = Zend_Registry::get('config')->version;

        $this->prependStylesheet(APPLICATION_PATH . '/../public/css/');

        $this->view->jquery();
        $this->appendJavaScript(APPLICATION_PATH . '/../public/js/page/');

        $paymentId = $this->_request->getParam('paymentId');
        $PayerID = $this->_request->getParam('PayerID');

        $playerId = $this->_auth->getIdentity()->playerId;

        if ($paymentId && $PayerID) {

            $apiContext = Coret_Model_PayPalApiContext::get('www');

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

    protected function prependStylesheet($path)
    {
        if ($handle = opendir($path)) {

            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && $entry != 'admin') {
                    $this->view->headLink()->prependStylesheet('/css/' . $entry);
                }
            }

            closedir($handle);
        }
    }

    protected function appendJavaScript($path)
    {
        if ($handle = opendir($path)) {

            while (false !== ($entry = readdir($handle))) {
                if ($entry == "." && $entry != "..") {
                    $this->view->headScript()->appendFile($path . $entry);
                }
            }

            closedir($handle);
        }
    }
}
