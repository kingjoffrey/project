<?php
use Devristo\Phpws\Protocol\WebSocketTransportInterface;

class HalloffameController
{
    public function index(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $view = new Zend_View();

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'halloffame',
            'action' => 'index',
            'data' => $view->render('halloffame/index.phtml'),
            'menu' => Zend_Registry::get('config')->gameType
        );
        $handler->sendToUser($user, $token);
    }

    public function getContent(WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn){
        $db = $handler->getDb();

        if (!isset($dataIn['m'])) {
            $dataIn['m'] = 3;
        }

        $m = new Application_Model_GameScore($db);

        $token = array(
            'type' => 'halloffame',
            'action' => 'content',
            'data' => $m->getHallOfFame($dataIn['m'])
        );
        $handler->sendToUser($user, $token);
    }
}