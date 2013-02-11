<?php

/**
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Game_Cli_WofHandler extends WebSocket_UriHandler {

    public function sendToChannel($token, $users, $debug = null) {
        if ($debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }
        foreach ($users AS $row)
        {
            foreach ($this->users AS $u)
            {
                if ($u->getId() == $row['webSocketServerUserId']) {
                    $this->send($u, Zend_Json::encode($token));
                }
            }
        }
    }

    public function sendError($user, $msg, $debug = null) {
        $token = array(
            'type' => 'error',
            'msg' => $msg
        );
        if ($debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }
        $this->send($user, Zend_Json::encode($token));
    }

}
