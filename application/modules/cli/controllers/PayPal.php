<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PaypalController
{
    public function create(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!$tournamentId = $dataIn['id']) {
            echo ('No tournamentId (create)') . "\n";
            return;
        }

        $apiContext = Coret_Model_PayPalApiContext::get();

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item1 = new Item();
        $item1->setName($dataIn['name'])
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setSku($dataIn['id'])// Similar to `item_number` in Classic API
            ->setPrice(1);

        $itemList = new ItemList();
        $itemList->setItems(array($item1));

        $details = new Details();
        $details->setShipping(0)
            ->setTax(0)
            ->setSubtotal(1);

        $amount = new Amount();
        $amount->setCurrency('USD')
            ->setTotal(1)
            ->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription('Tournament entry fee')
            ->setInvoiceNumber(uniqid());

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($dataIn['url'] . 'paypal/index/id/' . $tournamentId . '/')
            ->setCancelUrl($dataIn['url'] . 'paypal/index/id/' . $tournamentId . '/');

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        try {
            $payment->create($apiContext);
        } catch (Exception $ex) {
            echo 'PayPal create failed.' . "\n";
            return;
        }

        $db = $handler->getDb();

        $paymentId = $payment->getId();

        $mPayPal = new Application_Model_PayPal($db);
        $mPayPal->addPayment($paymentId, $user->parameters['playerId']);

        $mTournamentPlayers = new Application_Model_TournamentPlayers($db);
        $mTournamentPlayers->addPlayer($tournamentId, $user->parameters['playerId']);

        $token = array(
            'type' => 'tournament',
            'action' => 'create',
            'url' => $payment->getApprovalLink(),
        );
        $handler->sendToUser($user, $token);
    }
}