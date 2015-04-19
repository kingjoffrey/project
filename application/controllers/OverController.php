<?php

class OverController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $gameId = $this->_request->getParam('id');
        if (empty($gameId)) {
            throw new Exception('Brak game ID!');
        }

        $mGameScore = new Application_Model_GameScore();
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId);
        $this->view->score = $mGameScore->get($gameId);
        $this->view->players = $mPlayersInGame->getGamePlayers();
    }

}