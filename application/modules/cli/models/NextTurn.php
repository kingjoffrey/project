<?php

class Cli_Model_NextTurn
{
    protected $_db;
    protected $_gameHandler;
    protected $_user;
    protected $_game;
    protected $_players;

    public function __construct(IWebSocketConnection $user, Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {
        $this->_user = $user;
        $this->_game = $game;
        $this->_db = $db;
        $this->_gameHandler = $gameHandler;
        $this->_gameId = $this->_game->getId();
        $this->_players = $this->_game->getPlayers();

        while (true) {
            $nextPlayerId = $this->getExpectedNextTurnPlayer($this->_game, $this->_db);
            $nextPlayerColor = $this->_game->getPlayerColor($nextPlayerId);

            $player = $this->_players->getPlayer($nextPlayerColor);
            if ($player->armiesOrCastlesExists()) {

                $player->increaseAllCastlesProductionTurn($this->_gameId, $this->_db);

                $turnNumber = $this->_game->getTurnNumber();
                $turnsLimit = $this->_game->getTurnsLimit();

                if ($turnsLimit && $turnNumber > $turnsLimit) {
                    new Cli_Model_SaveResults($this->_gameId, $this->_db, $this->_gameHandler);
                    return;
                }

                $mTurnHistory = new Application_Model_TurnHistory($this->_gameId, $this->_db);
                $mTurnHistory->add($nextPlayerId, $turnNumber);

                $token = array(
                    'type' => 'nextTurn',
                    'nr' => $turnNumber,
                    'color' => $nextPlayerColor
                );
                $this->_gameHandler->sendToChannel($this->_db, $token, $this->_gameId);
                return;
            } else {
                $this->playerLost($nextPlayerColor);
            }
        }
    }

    private function playerLost($color)
    {
        $this->_players->getPlayer($color)->setLost($this->_gameId, $this->_db);
        $token = array(
            'type' => 'dead',
            'color' => $color
        );
        $this->_gameHandler->sendToChannel($this->_db, $token, $this->_gameId);

    }

    private function getExpectedNextTurnPlayer(Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db)
    {
        $playerColor = $game->getPlayerColor($game->getTurnPlayerId());
        $find = false;
        $playersInGameColors = $game->getPlayersInGameColors();


        reset($playersInGameColors);
        $firstColor = current($playersInGameColors);

        /* szukam następnego koloru w dostępnych kolorach */
        foreach ($playersInGameColors as $color) {
            /* znajduję kolor gracza, który ma aktualnie turę i przewijam na następny */
            if ($playerColor == $color) {
                $find = true;
                continue;
            }

            /* to jest przewinięty kolor gracza */
            if ($find) {
                $nextPlayerColor = $color;
                break;
            }
        }

        /* jeśli nie znalazłem następnego gracza to następnym graczem jest gracz pierwszy */
        if (!isset($nextPlayerColor)) {
            $nextPlayerColor = $firstColor;
        }

        /* jeżeli następny gracz to pierwszy gracz to wtedy nowa tura */
        if ($nextPlayerColor == $firstColor) {
            $game->turnNumberIncrement();
        }
        $turnPlayerId = $game->getPlayers()->getPlayer($nextPlayerColor)->getId();
        $game->setTurnPlayerId($turnPlayerId);

        $mGame = new Application_Model_Game($game->getId(), $db);
        $mGame->updateTurn($turnPlayerId, $game->getTurnNumber());

        return $turnPlayerId;
    }
}
