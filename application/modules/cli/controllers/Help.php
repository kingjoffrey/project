<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class HelpController
{
    function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $id_lang = Zend_Registry::get('id_lang');

        $db = $handler->getDb();

        if (!$help = $handler->getHelp($id_lang)) {
            echo 'not set' . "\n";
            $help = new Cli_Model_Help($db);
            $handler->addHelp($id_lang, $help);
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