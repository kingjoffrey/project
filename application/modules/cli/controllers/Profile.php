<?php

class ProfileController
{
    function index(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        $db = $handler->getDb();
        $mPlayer = new Application_Model_Player($db);
        $player = $mPlayer->getPlayer($user->parameters['playerId']);

        $view = new Zend_View();

        $view->formPlayer = new Application_Form_Player();
        $view->formPlayer->populate($player);
        $view->formPlayer->setView($view);

        $view->formEmail = new Application_Form_Email();
        $view->formEmail->populate($player);
        $view->formEmail->setView($view);

        $view->formPassword = new Application_Form_Password();
        $view->formPassword->setView($view);

        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');


        if (isset($dataIn['firstName']) || isset($dataIn['password']) || isset($dataIn['login'])) {
            $valid = true;

            if (isset($dataIn['firstName']) && $dataIn['firstName']) {
                if (!$view->formPlayer->isValid($dataIn)) {
                    $valid = false;
                }
            }
            if (isset($dataIn['password']) && $dataIn['password']) {
                if (!$view->formPassword->isValid($dataIn)) {
                    $valid = false;
                }
                unset($dataIn['repeatPassword']);
                $dataIn['password'] = md5($dataIn['password']);
            }
            if (isset($dataIn['login']) && $dataIn['login']) {
                if (!$view->formEmail->isValid($dataIn)) {
                    $valid = false;
                }
            }

            if ($valid) {
                $mPlayer->updatePlayer($dataIn, $user->parameters['playerId']);
                $token = array(
                    'type' => 'profile',
                    'action' => 'ok',
                    'data' => $view->render('profile/ok.phtml')
                );
            } else {
                $token = array(
                    'type' => 'profile',
                    'action' => 'index',
                    'data' => $view->render('profile/index.phtml')
                );
            }
        } else {
            $token = array(
                'type' => 'profile',
                'action' => 'index',
                'data' => $view->render('profile/index.phtml')
            );
        }
        $handler->sendToUser($user, $token);
    }

    function show(Devristo\Phpws\Protocol\WebSocketTransportInterface $user, Cli_MainHandler $handler, $dataIn)
    {
        if (!$playerId = $dataIn['id']) {
            return;
        }
        $db = $handler->getDb();
        $view = new Zend_View();

        $mPlayer = new Application_Model_Player($db);
        $view->player = $mPlayer->getPlayer($playerId);

        $mGameScore = new Application_Model_GameScore($db);
        $view->playerScores = $mGameScore->getPlayerScores($playerId);


        $view->addScriptPath(APPLICATION_PATH . '/views/scripts');

        $token = array(
            'type' => 'profile',
            'action' => 'show',
            'data' => $view->render('profile/show.phtml')
        );
        $handler->sendToUser($user, $token);
    }
}