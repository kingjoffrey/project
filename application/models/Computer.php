<?php

class Application_Model_Computer {

    static private function firstBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db = null) {
        if (!Application_Model_Database::enemiesCastlesExist($gameId, $playerId, $db)) {
            new Game_Logger('BRAK ZAMKÓW WROGA');
            self::secondBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
        } else {
            new Game_Logger('SĄ ZAMKI WROGA');
            $castleId = Game_Computer::getWeakerEnemyCastle($gameId, $castlesAndFields['hostileCastles'], $army, $playerId, $db);
            if ($castleId !== null) {
                new Game_Logger('JEST SŁABSZY ZAMEK WROGA');
                $castleRange = Game_Computer::isEnemyCastleInRange($castlesAndFields, $castleId, $army);
                if ($castleRange['in']) {
                    //atakuj
                    new Game_Logger('SŁABSZY ZAMEK WROGA W ZASIĘGU - ATAKUJ!');
                    $fightEnemy = Game_Computer::fightEnemy($army, null, $playerId, $castleId);
                    Application_Model_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $castleRange['currentPosition'], $db);
                    self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path'], $fightEnemy['battle'], $fightEnemy['victory'], $castleId);
                } else {
                    new Game_Logger('SŁABSZY ZAMEK WROGA POZA ZASIĘGIEM');
                    $enemy = Game_Computer::getWeakerEnemyArmyInRange($gameId, $enemies, $army, $castlesAndFields);
                    if ($enemy) {
                        //atakuj
                        new Game_Logger('JEST SŁABSZA ARMIA WROGA W ZASIĘGU');
                        $fightEnemy = Game_Computer::fightEnemy($army, $enemy, $playerId, $enemy['castleId']);
                        Application_Model_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $enemy['currentPosition'], $db);
                        self::endMove($playerId, $db, $gameId, $army['armyId'], $enemy['currentPosition'], $enemy['path'], $fightEnemy['battle'], $fightEnemy['victory'], $enemy['castleId'], null, $enemy['armyId']);
                    } else {
                        new Game_Logger('BRAK SŁABSZEJ ARMII WROGA W ZASIĘGU');
                        $enemy = Game_Computer::getStrongerEnemyArmyInRange($gameId, $enemies, $army, $castlesAndFields);
                        if ($enemy) {
                            new Game_Logger('JEST SILNIEJSZA ARMIA WROGA W ZASIĘGU');
                            $join = Game_Computer::getMyArmyInRange($army, $castlesAndFields['fields']);
                            if ($join) {
                                new Game_Logger('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                                Application_Model_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $join['currentPosition'], $db);
                                self::endMove($playerId, $db, $gameId, $army['armyId'], $join['currentPosition'], $join['path']);
                            } else {
                                new Game_Logger('BRAK MOJEJ ARMII W ZASIĘGU - IDŹ DO ZAMKU!');
                                Application_Model_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $castleRange['currentPosition'], $db);
                                Application_Model_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                                self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path']);
                            }
                        } else {
                            new Game_Logger('BRAK SILNIEJSZEJ ARMII WROGA W ZASIĘGU - IDŹ DO ZAMKU!');
                            Application_Model_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $castleRange['currentPosition'], $db);
                            Application_Model_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                            self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path']);
                        }
                    }
                }
            } else {
                new Game_Logger('BRAK SŁABSZYCH ZAMKÓW WROGA');
                self::secondBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
            }
        }
    }

    static private function secondBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db = null) {
        if (!$enemies) {
            throw new Exception('Wygrałem!?');
        } else {
            foreach ($enemies as $e)
            {
                $castleId = Application_Model_Board::isArmyInCastle($e['x'], $e['y'], $castlesAndFields['hostileCastles']);
                if (null !== $castleId) {
                    continue;
                }
                if (Game_Computer::isEnemyStronger($army, $e, $castleId)) {
                    continue;
                } else {
                    $enemy = $e;
                    break;
                }
            }
            if (isset($enemy)) {
                //atakuj
                new Game_Logger('WRÓG JEST SŁABSZY');
                $range = Game_Computer::isEnemyArmyInRange($castlesAndFields, $enemy, $army);
                if ($range['in']) {
                    new Game_Logger('SŁABSZY WRÓG W ZASIĘGU - ATAKUJ!');
                    $fightEnemy = Game_Computer::fightEnemy($army, $enemy, $playerId, $range['castleId']);
                    Application_Model_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $range['currentPosition'], $db);
                    self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path'], $fightEnemy['battle'], $fightEnemy['victory']);
                } else {
                    new Game_Logger('SŁABSZY WRÓG POZA ZASIĘGIEM - IDŹ DO WROGA');
                    Application_Model_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $range['currentPosition'], $db);
                    Application_Model_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                    self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path']);
                }
            } else {
                new Game_Logger('WRÓG JEST SILNIEJSZY');
                $join = Game_Computer::getMyArmyInRange($army, $castlesAndFields['fields']);
                if ($join) {
                    new Game_Logger('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                    Application_Model_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $join['currentPosition'], $db);
                    self::endMove($playerId, $db, $gameId, $army['armyId'], $join['currentPosition'], $join['path']);
                } else {
                    new Game_Logger('BRAK MOJEJ ARMII W ZASIĘGU');
                    $castle = Game_Computer::getMyCastelNearEnemy($enemies, $army, $castlesAndFields['fields'], $myCastles);
                    if ($castle) {
                        new Game_Logger('JEST MÓJ ZAMEK W POBLIŻU WROGA - IDŹ DO ZAMKU');
                        Application_Model_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $castle['currentPosition'], $db);
                        Application_Model_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                        self::endMove($playerId, $db, $gameId, $army['armyId'], $castle['currentPosition'], $castle['path']);
                    } else {
                        new Game_Logger('NIE MA MOJEGO ZAMKU W POBLIŻU WROGA - ZOSTAŃ');
                        Application_Model_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                        self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    }
                }
            }
        }
    }

    static private function ruinBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db = null) {
        if (empty($army['heroes'])) {
            new Game_Logger('BRAK HEROSA');
            self::firstBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
        } else {
            new Game_Logger('JEST HEROS');
            new Game_Logger($army['heroes'], 'HEROS:');
            $ruin = Game_Computer::getNearestRuin($castlesAndFields['fields'], Application_Model_Database::getFull($gameId, $db), $army);
            if (!$ruin) {
                new Game_Logger('BRAK RUIN');
                self::firstBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
            } else {
                //idź do ruin
                new Game_Logger('IDŹ DO RUIN');
                Application_Model_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $ruin['currentPosition'], $db);
                Application_Model_Database::addRuin($gameId, $ruin['ruinId'], $db);
                Application_Model_Database::searchRuin($gameId, $ruin['ruinId'], $army['heroes'][0]['heroId'], $army['armyId'], $playerId, $db);
                self::endMove($playerId, $db, $gameId, $army['armyId'], $ruin['currentPosition'], $ruin['path'], null, false, null, $ruin['ruinId']);
            }
        }
    }

    static public function moveArmy($gameId, $playerId, $army, $db = null) {
        new Game_Logger('');
        new Game_Logger($army['armyId'], 'armyId:');
        $canFlySwim = Game_Computer::getArmyCanFlySwim($army);
        $army['canFly'] = $canFlySwim['canFly'];
        $army['canSwim'] = $canFlySwim['canSwim'];
        $myCastles = Application_Model_Database::getPlayerCastles($gameId, $playerId, $db);
        $myCastleId = Application_Model_Board::isArmyInCastle($army['x'], $army['y'], $myCastles);
        $fields = Application_Model_Database::getEnemyArmiesFieldsPositions($gameId, $playerId, $db);
        $razed = Application_Model_Database::getRazedCastles($gameId, $db);
        $castlesAndFields = Application_Model_Board::prepareCastlesAndFields($fields, $razed, $myCastles);
        $enemies = Application_Model_Database::getAllEnemiesArmies($gameId, $playerId, $db);

        if ($myCastleId !== null) {
            new Game_Logger('W ZAMKU');
            $castlePosition = Application_Model_Board::getCastlePosition($myCastleId);
            $enemiesHaveRange = Game_Computer::canEnemyReachThisCastle($castlePosition, $castlesAndFields, $enemies);
            $enemiesInRange = Game_Computer::getEnemiesInRange($enemies, $army, $castlesAndFields['fields']);
            if (!$enemiesHaveRange) {
                new Game_Logger('BRAK WROGA Z ZASIĘGIEM');
                if (!$enemiesInRange) {
                    new Game_Logger('BRAK WROGA W ZASIĘGU');
                    self::ruinBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
                } else {
                    new Game_Logger('JEST WRÓG W ZASIĘGU');
                    foreach ($enemiesInRange as $e)
                    {
                        $castleId = Application_Model_Board::isArmyInCastle($e['x'], $e['y'], $castlesAndFields['hostileCastles']);
                        if (Game_Computer::isEnemyStronger($army, $e, $castleId)) {
                            continue;
                        } else {
                            $enemy = $e;
                            break;
                        }
                    }
                    if (isset($enemy)) {
                        new Game_Logger('WRÓG JEST SŁABSZY - ATAKUJ!');
                        //atakuj
                        if ($castleId !== null) {
                            $range = Game_Computer::isEnemyCastleInRange($castlesAndFields, $castleId, $army);
                        } else {
                            $range = Game_Computer::isEnemyArmyInRange($castlesAndFields, $enemy, $army);
                        }
                        $fightEnemy = Game_Computer::fightEnemy($army, $enemy, $playerId, $castleId);
                        Application_Model_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $range['currentPosition'], $db);
                        self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path'], $fightEnemy['battle'], $fightEnemy['victory'], $castleId);
                    } else {
                        new Game_Logger('WRÓG JEST SILNIEJSZY - ZOSTAŃ!');
                        Application_Model_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                        self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    }
                }
            } else {
                new Game_Logger('JEST WRÓG Z ZASIĘGIEM');
                if (!$enemiesInRange) {
                    new Game_Logger('BRAK WROGA W ZASIĘGU - ZOSTAŃ!');
                    Application_Model_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                    self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                } else {
                    new Game_Logger('JEST WRÓG W ZASIĘGU');
                    if (count($enemiesHaveRange) > count($enemiesInRange)) {
                        new Game_Logger('WRÓGÓW Z ZASIĘGIEM > WRÓGÓW W ZASIĘGU - ZOSTAŃ!');
                        Application_Model_Database::zeroArmyMovesLeft($army['armyId'], $db);
                        self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    } else {
                        new Game_Logger('WRÓGÓW Z ZASIĘGIEM <= WRÓGÓW W ZASIĘGU');
                        $enemy = Game_Computer::canAttackAllEnemyHaveRange($enemiesHaveRange, $army, $castlesAndFields['hostileCastles']);
                        if (!$enemy) {
                            new Game_Logger('NIE MOGĘ ZAATAKOWAĆ WRÓGÓW Z ZASIĘGIEM - ZOSTAŃ!');
                            Application_Model_Database::zeroArmyMovesLeft($army['armyId'], $db);
                            self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                        } else {
                            //atakuj
                            new Game_Logger('ATAKUJĘ WRÓGÓW Z ZASIĘGIEM - ATAKUJ!'); //atakuję wrogów którzy mają zasięg na zamek, brak enemy armyId, armia nie zmienia pozycji
                            $aStar = $enemy['aStar'];
                            $aStar->restorePath($enemy['key'], $enemy['movesToSpend']);
                            $path = $aStar->reversePath();
                            $currentPosition = $aStar->getCurrentPosition();
                            $fightEnemy = Game_Computer::fightEnemy($army, $enemy, $playerId, $enemy['castleId']);
                            Application_Model_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $currentPosition, $db);
                            self::endMove($playerId, $db, $gameId, $army['armyId'], $currentPosition, $path, $fightEnemy['battle'], $fightEnemy['victory'], $enemy['castleId'], null, $enemy['armyId']);
                        }
                    }
                }
            }
        } else {
            new Game_Logger('POZA ZAMKIEM');
            $myEmptyCastle = Game_Computer::getMyEmptyCastleInMyRange($myCastles, $army, $castlesAndFields['fields']);
            if (!$myEmptyCastle) {
                new Game_Logger('NIE MA PUSTEGO ZAMKU W ZASIĘGU');
                self::ruinBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
            } else {
                new Game_Logger('JEST PUSTY ZAMEK W ZASIĘGU');
                if (!Game_Computer::isMyCastleInRangeOfEnemy($enemies, $myEmptyCastle, $castlesAndFields['fields'])) {
                    new Game_Logger('WRÓG NIE MA ZASIĘGU NA PUSTY ZAMEK');
                    self::firstBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
                } else {
                    //idź do zamku
                    new Game_Logger('WRÓG MA ZASIĘG NA PUSTY ZAMEK - IDŹ DO ZAMKU!');
                    $data = array(
                        'x' => $myEmptyCastle['x'],
                        'y' => $myEmptyCastle['y'],
                        'movesSpend' => $army['movesLeft']
                    );
                    Application_Model_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $data, $db);
                    self::endMove($playerId, $db, $gameId, $army['armyId'], $myEmptyCastle['currentPosition'], $myEmptyCastle['path']);
                }
            }
        }
    }

    static private function endMove($playerId, $db, $gameId, $oldArmyId, $position, $path = null, $battle = null, $victory = false, $castleId = null, $ruinId = null, $enemyArmyId = null) {
        $armiesIds = Application_Model_Database::joinArmiesAtPosition($gameId, $position, $playerId, $db);
        $armyId = $armiesIds[0]['armyId'];
        if (!$armyId) {
            $armyId = $oldArmyId;
        }
        $army = Application_Model_Database::getArmyByArmyIdPlayerId($gameId, $armyId, $playerId, $db);

        $army['action'] = 'continue';
        $army['oldArmyId'] = $oldArmyId;
        if ($castleId !== null) {
            $army['castleId'] = $castleId;
        }

//        $mWebSocket = new Application_Model_WebSocket();
//        $mWebSocket->authorizeChannel($this->_namespace->wsKeys);
//        $color = $this->_mGame->getPlayerColor($this->_namespace->player['playerId']);

        if ($ruinId !== null) {
            $army['ruinId'] = $ruinId;
//            $mWebSocket->publishChannel($this->_namespace->gameId, $color . '.r.' . $ruinId . '.' . 1);
        }
        if ($enemyArmyId) {
            $army['enemyArmyId'] = $enemyArmyId;
        }
        if (!empty($path)) {
            $army['path'] = $path;
        }
        $army['victory'] = $victory;
        if (!empty($battle)) {
            $army['battle'] = $battle;
        }

        return $army;

//        $mWebSocket->publishChannel($this->_namespace->gameId, $color . '.A.' . $this->_mGame->getPlayerColor($this->playerId));
//        $mWebSocket->close();
    }

    static public function endTurn($gameId, $playerId, $db = null) {
        $youWin = false;
        $response = array();
        $nextPlayer = array(
            'color' => Application_Model_Database::getPlayerColor($gameId, $playerId, $db)
        );
        while (empty($response))
        {
            $nextPlayer = Application_Model_Database::nextTurn($gameId, $nextPlayer['color'], $db);
            $playerCastlesExists = Application_Model_Database::playerCastlesExists($gameId, $nextPlayer['playerId'], $db);
            $playerArmiesExists = Application_Model_Database::playerArmiesExists($gameId, $nextPlayer['playerId'], $db);
            if ($playerCastlesExists || $playerArmiesExists) {
                $response = $nextPlayer;
                if ($nextPlayer['playerId'] == $playerId) {
                    $youWin = true;
                    Application_Model_Database::endGame($gameId, $db);
                } else {
                    $nr = Application_Model_Database::updateTurnNumber($gameId, $nextPlayer['playerId'], $db);
                    if ($nr) {
                        $response['nr'] = $nr;
                    }
                    Application_Model_Database::raiseAllCastlesProductionTurn($gameId, $playerId, $db);
//                    $mWebSocket = new Application_Model_WebSocket();
//                    $mWebSocket->authorizeChannel($this->_namespace->wsKeys);
//                    $nextTurn = $this->_mGame->getTurn();
//                    $mWebSocket->publishChannel($this->_namespace->gameId, $this->_mGame->getPlayerColor($this->_namespace->player['playerId']) . '.t.' . $nextTurn['color'] . '.' . $nextTurn['nr'] . '.' . $nextTurn['lost']);
//                    $mWebSocket->close();
                }
                $response['win'] = $youWin;
            } else {
                Application_Model_Database::setPlayerLostGame($gameId, $nextPlayer['playerId'], $db);
            }
        }
        $response['action'] = 'end';

        return $response;
    }

    static public function startTurn($gameId, $playerId, $db = null) {
        Application_Model_Database::turnActivate($gameId, $playerId, $db);
        $castles = array();
        Application_Model_Database::resetHeroesMovesLeft($gameId, $playerId, $db);
        Application_Model_Database::resetSoldiersMovesLeft($gameId, $playerId, $db);
        $gold = Application_Model_Database::getPlayerInGameGold($gameId, $playerId, $db);
        $income = 0;
        $costs = 0;
        $turnNumber = Application_Model_Database::getTurnNumber($gameId, $db);
        if ($turnNumber > 0) {
            $castlesId = Application_Model_Database::getPlayerCastles($gameId, $playerId, $db);
            foreach ($castlesId as $id)
            {
                $castleId = $id['castleId'];
                $castles[$castleId] = Application_Model_Board::getCastle($castleId);
                $castle = $castles[$castleId];
                $income += $castle['income'];
                $castleProduction = Application_Model_Database::getCastleProduction($gameId, $castleId, $playerId, $db);
                if ($turnNumber < 10) {
                    $unitName = Application_Model_Board::getMinProductionTimeUnit($castleId);
                } else {
                    $unitName = Application_Model_Board::getCastleOptimalProduction($castleId);
                }
                $modelUnit = new Application_Model_Unit();
                $unitId = $modelUnit->getUnitIdByName($unitName);
                if ($unitId != $castleProduction['production']) {
                    Application_Model_Board::setCastleProduction($gameId, $castleId, $unitId, $playerId, $db);
                    $castleProduction = Application_Model_Database::getCastleProduction($gameId, $castleId, $playerId, $db);
                }
                $castles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];
                $unitName = Application_Model_Board::getUnitName($castleProduction['production']);
                if ($castle['production'][$unitName]['time'] <= $castleProduction['productionTurn'] AND $castle['production'][$unitName]['cost'] <= $gold) {
                    if (Application_Model_Database::resetProductionTurn($gameId, $castleId, $playerId, $db) == 1) {
                        $armyId = Application_Model_Database::getArmyIdFromPosition($gameId, $castle['position'], $db);
                        if (!$armyId) {
                            $armyId = Application_Model_Database::createArmy($gameId, $castle['position'], $playerId, $db);
                        }
                        Application_Model_Database::addSoldierToArmy($gameId, $armyId, $castleProduction['production'], $db);
                    }
                }
            }
            if (isset($castle['position'])) {
                $gold = self::handleHeroResurrection($gameId, $gold, $castle['position'], $playerId, $db);
            }
            $armies = Application_Model_Database::getPlayerArmies($gameId, $playerId, $db);
            if (empty($castles) && empty($armies)) {
                return 'gameover';
            } else {
                foreach ($armies as $army)
                {
                    foreach ($army['soldiers'] as $unit)
                    {
                        $costs += $unit['cost'];
                    }
                }
                $gold = $gold + $income - $costs;
                Application_Model_Database::updatePlayerInGameGold($gameId, $playerId, $gold, $db);
                return 'continue';
            }
        }
    }

    static private function handleHeroResurrection($gameId, $gold, $position, $playerId, $db = null) {
        if (!Application_Model_Database::isHeroInGame($gameId, $playerId, $db)) {
            Application_Model_Database::connectHero($gameId, $playerId, $db);
        }
        $heroId = Application_Model_Database::getDeadHeroId($gameId, $playerId, $db);
        if ($heroId) {
            if ($gold >= 100) {
                $armyId = Application_Model_Database::heroResurection($gameId, $heroId, $position, $playerId, $db);
                if ($armyId) {
                    return $gold - 100;
                }
            }
        }
        return $gold;
    }

}

