<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;
class HelpController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler)
    {
        $db = $handler->getDb();
        if (!$help = $handler->getHelp()) {
            echo 'not set' . "\n";
            $help = new Cli_Model_Help($db);
            $handler->addHelp($help);
        }

        $view = new Zend_View();
        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'help',
            'action' => 'index',
            'data' => $view->render('help/index.phtml'),

        );
        $token = array_merge($token, $help->toArray());
        $handler->sendToUser($user, $token);
    }
}