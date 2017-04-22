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

class TournamentController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $db = $handler->getDb();
        $mTournament = new Application_Model_Tournament($db);

        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'tournament',
            'action' => 'index',
            'data' => $view->render('tournament/index.phtml'),
            'list' => $mTournament->getList()
        );
        $handler->sendToUser($user, $token);
    }

    function show(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!$tournamentId = $dataIn['id']) {
            echo ('No tournamentId (show)') . "\n";
            return;
        }

        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $db = $handler->getDb();
        $mTournamentPlayers = new Application_Model_TournamentPlayers($db);
        if ($mTournamentPlayers->checkPlayer($tournamentId, $user->parameters['playerId'])) {
            $data = $view->render('tournament/show.phtml');
            $action = 'show';
        } else {
            $data = $view->render('tournament/paypal.phtml');
            $action = 'paypal';
        }

        $token = array(
            'type' => 'tournament',
            'action' => $action,
            'id' => $tournamentId,
            'data' => $data,
        );
        $handler->sendToUser($user, $token);
    }

    public function create(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!$tournamentId = $dataIn['id']) {
            echo ('No tournamentId (create)') . "\n";
            return;
        }

        $payPalConfig = Zend_Registry::get('config')->paypal;

        $apiContext = $this->getApiContext($payPalConfig->clientId, $payPalConfig->clientSecret);

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
        $redirectUrls->setReturnUrl($dataIn['url'] . 'paypal/id/' . $tournamentId . '/')
            ->setCancelUrl($dataIn['url'] . 'paypal/id/' . $tournamentId . '/');

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

    private function getApiContext($clientId, $clientSecret)
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $clientId,
                $clientSecret
            )
        );

        $apiContext->setConfig(
            array(
                'mode' => 'sandbox',
                'log.LogEnabled' => true,
                'log.FileName' => APPLICATION_PATH . '/../log/PayPal_CLI.log',
                'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                'cache.enabled' => false,
            )
        );

        return $apiContext;
    }
}