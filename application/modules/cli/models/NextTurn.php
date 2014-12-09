<?php

class Cli_Model_NextTurn
{

    public function __construct(Cli_Model_Game $game, Zend_Db_Adapter_Pdo_Pgsql $db, Cli_GameHumansHandler $gameHandler)
    {

        $gameId = $game->getId();
        $players = $game->getPlayers();

        while (true) {
            $nextPlayerId = $this->getExpectedNextTurnPlayer($game, $db);
            $nextPlayerColor = $game->getPlayerColor($nextPlayerId);

            $player = $players->getPlayer($nextPlayerColor);
            if ($player->armiesOrCastlesExists()) {

                $player->increaseAllCastlesProductionTurn($gameId, $db);

                $turnNumber = $game->getTurnNumber();
                $turnsLimit = $game->getTurnsLimit();

                if ($turnsLimit && $turnNumber > $turnsLimit) {
                    new Cli_Model_SaveResults($gameId, $db, $gameHandler);
                    return;
                }

                $mTurnHistory = new Application_Model_TurnHistory($gameId, $db);
                $mTurnHistory->add($nextPlayerId, $turnNumber);

                $token = array(
                    'type' => 'nextTurn',
                    'nr' => $turnNumber,
                    'color' => $nextPlayerColor
                );
                $gameHandler->sendToChannel($db, $token, $gameId);
                return;
            } else {
                $players->getPlayer($nextPlayerColor)->setLost($gameId, $db);
                $token = array(
                    'type' => 'dead',
                    'color' => $nextPlayerColor
                );
                $gameHandler->sendToChannel($db, $token, $gameId);
            }
        }
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
