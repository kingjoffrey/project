<?php

class Cli_Model_ComputerMainBlocks extends Cli_Model_ComputerSubBlocks
{

    public function __construct($gameId, $playerId, $db)
    {
        $this->_gameId = $gameId;
        $this->_playerId = $playerId;
        $this->_db = $db;

        $this->_l = new Coret_Model_Logger();
        $this->_modelArmy = new Application_Model_Army($this->_gameId, $this->_db);
        $this->_mGame = new Application_Model_Game($this->_gameId, $this->_db);
    }

    private function firstBlock($enemies, $mArmy, $castlesAndFields)
    {
        $army = $mArmy->getArmy();
        $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);
        if (!$mCastlesInGame->enemiesCastlesExist($this->_playerId)) {
            $this->_l->log('BRAK ZAMKÓW WROGA');
            return self::secondBlock($enemies, $mArmy, $castlesAndFields);
        } else {
            $this->_l->log('SĄ ZAMKI WROGA');

            $castleIds = array();
            $castleId = $this->getWeakerEnemyCastle($castlesAndFields['hostileCastles'], $army);
            if ($castleId) {
                $castleRange = $this->isEnemyCastleInRange($castlesAndFields, $castleId, $mArmy);
                while (true) {
                    if (empty($castleRange)) {
                        $castleIds[] = $castleId;
                        $castleId = $this->getWeakerEnemyCastle($castlesAndFields['hostileCastles'], $army, $castleIds);
                        if ($castleId) {
                            $castleRange = $this->isEnemyCastleInRange($castlesAndFields, $castleId, $mArmy);
                        } else {
                            break;
                        }
                    }
                    break;
                }
            }

            if ($castleId) {
                $this->_l->log('JEST SŁABSZY ZAMEK WROGA: ' . $castleId);

                if ($castleRange['in']) {
                    //atakuj
                    $this->_l->log('SŁABSZY ZAMEK WROGA W ZASIĘGU - ATAKUJĘ!');
                    $fightEnemyResults = $this->fightEnemy($army, $castleRange['path'], $castlesAndFields['fields'], null, $castleId);
                    return $this->endMove($army['armyId'], $castleRange['currentPosition'], $castleRange['path'], $fightEnemyResults, $castleId);
                } else {
                    $this->_l->log('SŁABSZY ZAMEK WROGA POZA ZASIĘGIEM');
                    $enemy = $this->getWeakerEnemyArmyInRange($enemies, $mArmy, $castlesAndFields);
                    if ($enemy) {
                        //atakuj
                        $this->_l->log('JEST SŁABSZA ARMIA WROGA W ZASIĘGU (' . $enemy['armyId'] . ') - ATAKUJĘ!');
                        $fightEnemyResults = $this->fightEnemy($army, $enemy['path'], $castlesAndFields['fields'], $enemy, $enemy['castleId']);
                        return $this->endMove($army['armyId'], $enemy['currentPosition'], $enemy['path'], $fightEnemyResults, $enemy['castleId']);
                    } else {
                        $this->_l->log('BRAK SŁABSZEJ ARMII WROGA W ZASIĘGU');
                        $enemy = $this->getStrongerEnemyArmyInRange($enemies, $mArmy, $castlesAndFields);
                        if ($enemy) {
                            $this->_l->log('JEST SILNIEJSZA ARMIA WROGA W ZASIĘGU: ' . $enemy['armyId']);
                            $join = $this->getMyArmyInRange($mArmy, $castlesAndFields);
                            if ($join) {
                                $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                                Cli_Model_Army::updateArmyPosition($this->_playerId, $join['path'], $castlesAndFields['fields'], $army, $this->_gameId, $this->_db);
                                return $this->endMove($army['armyId'], $join['currentPosition'], $join['path']);
                            } else {
                                $this->_l->log('BRAK MOJEJ ARMII W ZASIĘGU - IDŹ W KIERUNKU ZAMKU!');
                                Cli_Model_Army::updateArmyPosition($this->_playerId, $castleRange['path'], $castlesAndFields['fields'], $army, $this->_gameId, $this->_db);
                                return $this->endMove($army['armyId'], $castleRange['currentPosition'], $castleRange['path']);
                            }
                        } else {
                            $this->_l->log('BRAK SILNIEJSZEJ ARMII WROGA W ZASIĘGU');
                            $join = $this->getMyArmyInRange($mArmy, $castlesAndFields);
                            if ($join) {
                                $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                                Cli_Model_Army::updateArmyPosition($this->_playerId, $join['path'], $castlesAndFields['fields'], $army, $this->_gameId, $this->_db);
                                return $this->endMove($army['armyId'], $join['currentPosition'], $join['path']);
                            } else {
                                $this->_l->log('BRAK MOJEJ ARMII W ZASIĘGU - IDŹ W KIERUNKU ZAMKU!');
                                Cli_Model_Army::updateArmyPosition($this->_playerId, $castleRange['path'], $castlesAndFields['fields'], $army, $this->_gameId, $this->_db);
                                $this->_modelArmy->fortify($army['armyId'], 1);
                                return $this->endMove($army['armyId'], $castleRange['currentPosition'], $castleRange['path']);
                            }
                        }
                    }
                }
            } else {
                $this->_l->log('BRAK SŁABSZYCH ZAMKÓW WROGA');
                return $this->secondBlock($enemies, $mArmy, $castlesAndFields);
            }
        }
    }

    private function secondBlock($enemies, $mArmy, $castlesAndFields)
    {
        $army = $mArmy->getArmy();
        if (!$enemies) {
            return array(
                'action' => 'end'
            );
        } else {
            foreach ($enemies as $e) {
                $castleId = Application_Model_Board::isCastleAtPosition($e['x'], $e['y'], $castlesAndFields['hostileCastles']);
                if (null !== $castleId) {
                    continue;
                }
                if ($this->isEnemyStronger($army, $e, $castleId)) {
                    continue;
                } else {
                    $enemy = $e;
                    break;
                }
            }
            if (isset($enemy)) {
                //atakuj
                $this->_l->log('WRÓG JEST SŁABSZY');
                $range = $this->isEnemyArmyInRange($castlesAndFields, $enemy, $mArmy);
                if ($range['in']) {
                    $this->_l->log('SŁABSZY WRÓG W ZASIĘGU - ATAKUJ!');
                    $fightEnemyResults = $this->fightEnemy($army, $range['path'], $castlesAndFields['fields'], $enemy, $range['castleId']);
                    return $this->endMove($army['armyId'], $range['currentPosition'], $range['path'], $fightEnemyResults);
                } else {
                    $this->_l->log('SŁABSZY WRÓG POZA ZASIĘGIEM - IDŹ DO WROGA');
                    Cli_Model_Army::updateArmyPosition($this->_playerId, $range['path'], $castlesAndFields['fields'], $army, $this->_gameId, $this->_db);
                    $this->_modelArmy->fortify($army['armyId'], 1);
                    return $this->endMove($army['armyId'], $range['currentPosition'], $range['path']);
                }
            } else {
                $this->_l->log('WRÓG JEST SILNIEJSZY');
                $join = $this->getMyArmyInRange($mArmy, $castlesAndFields);
                if ($join) {
                    $this->_l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                    Cli_Model_Army::updateArmyPosition($this->_playerId, $join['path'], $castlesAndFields['fields'], $army, $this->_gameId, $this->_db);
                    return $this->endMove($army['armyId'], $join['currentPosition'], $join['path']);
                } else {
                    $this->_l->log('BRAK MOJEJ ARMII W ZASIĘGU');
                    $castle = $this->getMyCastleNearEnemy($enemies, $mArmy, $castlesAndFields);
                    if ($castle) {
                        $this->_l->log('JEST MÓJ ZAMEK W POBLIŻU WROGA - IDŹ DO ZAMKU');
                        Cli_Model_Army::updateArmyPosition($this->_playerId, $castle['path'], $castlesAndFields['fields'], $army, $this->_gameId, $this->_db);
                        $this->_modelArmy->fortify($army['armyId'], 1);
                        return $this->endMove($army['armyId'], $castle['currentPosition'], $castle['path']);
                    } else {
                        $this->_l->log('NIE MA MOJEGO ZAMKU W POBLIŻU WROGA - ZOSTAŃ');
                        $this->_modelArmy->fortify($army['armyId'], 1);
                        return $this->endMove($army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    }
                }
            }
        }
    }

    private function ruinBlock($enemies, $mArmy, $castlesAndFields)
    {
        $army = $mArmy->getArmy();
        if (empty($army['heroes'])) {
            $this->_l->log('BRAK HEROSA');

            return $this->firstBlock($enemies, $mArmy, $castlesAndFields);
        } else {
            $this->_l->log('JEST HEROS');

            $mRuinsInGame = new Application_Model_RuinsInGame($this->_gameId, $this->_db);
            $ruin = $this->getNearestRuin($castlesAndFields['fields'], $mRuinsInGame->getFullRuins(), $mArmy);

            if (!$ruin) {
                $this->_l->log('BRAK RUIN');

                return $this->firstBlock($enemies, $mArmy, $castlesAndFields);
            } else {
                $this->_l->log('IDŹ DO RUIN');

                Cli_Model_Army::updateArmyPosition($this->_playerId, $ruin['path'], $castlesAndFields['fields'], $army, $this->_gameId, $this->_db);
                Cli_Model_SearchRuin::search($this->_gameId, $ruin['ruinId'], $army['heroes'][0]['heroId'], $army['armyId'], $this->_playerId, $this->_db);

                $this->_modelArmy->fortify($army['armyId'], 1);
                return $this->endMove($army['armyId'], $ruin['currentPosition'], $ruin['path'], null, null, $ruin['ruinId']);
            }
        }
    }

    public function moveArmy($mArmy, $user, $gameHandler)
    {
        $army = $mArmy->getArmy();
        $this->_l->log('');
        $this->_l->log($army['armyId'], 'armyId: ');

        $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);
        $myCastles = $mCastlesInGame->getPlayerCastles($this->_playerId);

//        $mapCastles = Zend_Registry::get('castles');
//        foreach ($myCastles as $myCastleId => $myCastle) {
//            $myCastles[$myCastleId]['position'] = $mapCastles[$myCastleId]['position'];
//        }

        $fields = $this->_modelArmy->getEnemyArmiesFieldsPositions($this->_playerId);
        $razed = $mCastlesInGame->getRazedCastles();
        $castlesAndFields = Application_Model_Board::prepareCastlesAndFields($fields, $razed, $myCastles);
        $myCastleId = Application_Model_Board::isCastleAtPosition($army['x'], $army['y'], $castlesAndFields['myCastles']);


        $enemies = $this->_modelArmy->getAllEnemiesArmies($this->_playerId);

        if ($myCastleId !== null) {
            $this->_l->log('W ZAMKU');

            $castlePosition = $castlesAndFields['myCastles'][$myCastleId]['position'];

            $turnNumber = $this->_mGame->getTurnNumber();
            $numberOfUnits = floor($turnNumber / 7);
            if ($numberOfUnits > 4) {
                $numberOfUnits = 4;
            }

            if ($numberOfUnits) {
                $garrison = Cli_Model_Army::getArmiesFromCastlePosition($castlePosition, $this->_gameId, $this->_db);
                reset($garrison);
                $armyId = Cli_Model_Army::isCastleGarrisonSufficient($numberOfUnits, $garrison);

                if ($armyId) {
                    $this->_modelArmy->fortify($armyId, 1);
                    if (count($garrison) > 1) {
                        $notGarrison = array();
                        foreach ($garrison as $army) {
                            if ($armyId == $army['armyId']) {
                                continue;
                            }
                            $notGarrison[] = $army;
                        }

                        if (count($notGarrison) > 1) {
                            $this->_l->log('ŁĄCZĘ ARMIE, KTÓRE PÓJDĄ DALEJ');

                            $firstArmy = current($notGarrison);
                            $path = array(0 => array(
                                'x' => $firstArmy['x'],
                                'y' => $firstArmy['y'],
                                'tt' => 'c')
                            );
                            $secondArmy = next($notGarrison);

                            $cliModelArmy = new Cli_Model_Army($secondArmy);
                            Cli_Model_Army::updateArmyPosition($this->_playerId, $path, $castlesAndFields['fields'], $cliModelArmy->getArmy(), $this->_gameId, $this->_db);
                            return $this->endMove($secondArmy['armyId'], $path[0], $path);
                        } elseif (count($notGarrison) == 1) {
                            $this->_l->log('TA ARMIA IDZIE DALEJ');

                            $mArmy = new Cli_Model_Army(current($notGarrison));
                            $army = $mArmy->getArmy();
                        }
                    } else {
                        $this->_l->log('OBSADA ZAMKU - ZOSTAŃ!');

                        $army = current($garrison);

                        return $this->endMove($armyId, $army);
                    }
                } elseif (count($garrison) > 1) {
                    $this->_l->log('ŁĄCZĘ ARMIE W JEDNĄ');

                    $firstArmy = current($garrison);
                    $path = array(0 => array(
                        'x' => $firstArmy['x'],
                        'y' => $firstArmy['y'],
                        'tt' => 'c')
                    );
                    $secondArmy = next($garrison);

                    $cliModelArmy = new Cli_Model_Army($secondArmy);
                    Cli_Model_Army::updateArmyPosition($this->_playerId, $path, $castlesAndFields['fields'], $cliModelArmy->getArmy(), $this->_gameId, $this->_db);
                    return $this->endMove($secondArmy['armyId'], $path[0], $path);
                } else {
                    $army = current($garrison);
                    if (count($army['soldiers']) > $numberOfUnits) {
                        $this->_l->log('ARMIA W ZAMKU MA WIĘCEJ JEDNOSTEK NIŻ JEST TO WYMAGANE');

                        $h = '';
                        $s = '';
                        $counter = count($army['soldiers']) - $numberOfUnits;

                        foreach ($army['heroes'] as $hero) {
                            if ($h) {
                                $h .= ',' . $hero['heroId'];
                            } else {
                                $h = $hero['heroId'];
                            }
                        }

                        foreach ($army['soldiers'] as $soldier) {
                            $counter--;
                            if ($counter < 0) {
                                break;
                            }
                            if ($s) {
                                $s .= ',' . $soldier['soldierId'];
                            } else {
                                $s = $soldier['soldierId'];
                            }
                        }

                        $mSplitArmy = new Cli_Model_SplitArmy();
                        $newArmyId = $mSplitArmy->split($army['armyId'], $s, $h, $user, $this->_playerId, $this->_db, $gameHandler);

                        if ($army['x'] == $castlePosition['x'] && $army['y'] == $castlePosition['y']) {
                            $path = array(0 => array(
                                'x' => $castlePosition['x'] + 1,
                                'y' => $castlePosition['y'] + 1,
                                'tt' => 'c')
                            );
                        } else {
                            $path = array(0 => array(
                                'x' => $castlePosition['x'],
                                'y' => $castlePosition['y'],
                                'tt' => 'c')
                            );
                        }

                        $army = Cli_Model_Army::getArmyByArmyId($newArmyId, $this->_gameId, $this->_db);
                        $cliModelArmy = new Cli_Model_Army($army);
                        Cli_Model_Army::updateArmyPosition($this->_playerId, $path, $castlesAndFields['fields'], $cliModelArmy->getArmy(), $this->_gameId, $this->_db);
                        return $this->endMove($newArmyId, $path[0], $path);
                    } else {
                        $this->_l->log('ZA MAŁA OBSADA ZAMKU - ZOSTAŃ!');

                        $this->_modelArmy->fortify($army['armyId'], 1);
                        return $this->endMove($army['armyId'], $army);
                    }
                }
            }

            $enemiesHaveRange = $this->getEnemiesHaveRangeAtThisCastle($castlePosition, $castlesAndFields, $enemies);
            $enemiesInRange = $this->getEnemiesInRange($enemies, $mArmy, $castlesAndFields['fields']);
            if (!$enemiesHaveRange) {
                $this->_l->log('BRAK WROGA Z ZASIĘGIEM');

                if (!$enemiesInRange) {
                    $this->_l->log('BRAK WROGA W ZASIĘGU');

                    return $this->ruinBlock($enemies, $mArmy, $castlesAndFields, $castlesAndFields['myCastles']);
                } else {
                    $this->_l->log('JEST WRÓG W ZASIĘGU');

                    foreach ($enemiesInRange as $e) {
                        $castleId = Application_Model_Board::isCastleAtPosition($e['x'], $e['y'], $castlesAndFields['hostileCastles']);
                        if ($this->isEnemyStronger($army, $e, $castleId)) {
                            continue;
                        } else {
                            $enemy = $e;
                            break;
                        }
                    }
                    if (isset($enemy)) {
                        $this->_l->log('WRÓG JEST SŁABSZY - ATAKUJ!');

                        if ($castleId !== null) {
                            $range = $this->isEnemyCastleInRange($castlesAndFields, $castleId, $mArmy);
                        } else {
                            $range = $this->isEnemyArmyInRange($castlesAndFields, $enemy, $mArmy);
                        }
                        $fightEnemyResults = $this->fightEnemy($army, $range['path'], $castlesAndFields['fields'], $enemy, $castleId);
                        return $this->endMove($army['armyId'], $range['currentPosition'], $range['path'], $fightEnemyResults, $castleId);
                    } else {
                        $this->_l->log('WRÓG JEST SILNIEJSZY - ZOSTAŃ!');

                        $this->_modelArmy->fortify($army['armyId'], 1);
                        return $this->endMove($army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    }
                }
            } else {
                $this->_l->log('JEST WRÓG Z ZASIĘGIEM');

                if ($turnNumber <= 7 && !$enemiesInRange) {
                    $this->_l->log('BRAK WROGA W ZASIĘGU I TURA < 8 - ZOSTAŃ!');

                    $this->_modelArmy->fortify($army['armyId'], 1);
                    return $this->endMove($army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                } else {
                    $this->_l->log('JEST WRÓG W ZASIĘGU');

                    if ($turnNumber <= 7 && count($enemiesHaveRange) > 1) {
                        $this->_l->log('WRÓGÓW Z ZASIĘGIEM > WRÓGÓW W ZASIĘGU - ZOSTAŃ!');

                        $this->_modelArmy->fortify($army['armyId'], 1);
                        return $this->endMove($army['armyId'], $army);
                    } else {
                        $this->_l->log('TYLKO JEDEN Z WRÓGÓW Z ZASIĘGIEM LUB TURA > 7');

                        $enemy = $this->canAttackAllEnemyHaveRange($enemiesHaveRange, $army, $castlesAndFields['hostileCastles']);
                        if (!$enemy) {
                            $this->_l->log('NIE MOGĘ ZAATAKOWAĆ WRÓGÓW Z ZASIĘGIEM - ZOSTAŃ!');

                            $this->_modelArmy->fortify($army['armyId'], 1);
                            return $this->endMove($army['armyId'], $army);
                        } else {
                            $range = $this->isEnemyArmyInRange($castlesAndFields, $enemy, $mArmy);
                            if ($range['in']) {
                                $this->_l->log('ATAKUJĘ WRÓGA Z ZASIĘGIEM - ATAKUJ!');

                                $fightEnemyResults = $this->fightEnemy($army, $range['path'], $castlesAndFields['fields'], $enemy, $range['castleId']);
                                return $this->endMove($army['armyId'], $range['currentPosition'], $range['path'], $fightEnemyResults, $range['castleId']);
                            } else {
                                $this->_l->log('WRÓG Z ZASIĘGIEM POZA ZASIĘGIEM - IDŹ DO WROGA!');

                                Cli_Model_Army::updateArmyPosition($this->_playerId, $range['path'], $castlesAndFields['fields'], $army, $this->_gameId, $this->_db);
                                $this->_modelArmy->fortify($army['armyId'], 1);
                                return $this->endMove($army['armyId'], $range['currentPosition'], $range['path']);
                            }
                        }
                    }
                }
            }
        } else {
            $this->_l->log('POZA ZAMKIEM');

            $myEmptyCastle = $this->getMyEmptyCastleInMyRange($mArmy, $castlesAndFields);
            if (!$myEmptyCastle) {
                $this->_l->log('NIE MA MOJEGO PUSTEGO ZAMKU W ZASIĘGU');

                return $this->ruinBlock($enemies, $mArmy, $castlesAndFields, $castlesAndFields['myCastles']);
            } else {
                $this->_l->log('JEST MÓJ PUSTY ZAMEK W ZASIĘGU');

                if (!$this->isMyCastleInRangeOfEnemy($enemies, $myEmptyCastle, $castlesAndFields['fields'])) {
                    $this->_l->log('WRÓG NIE MA ZASIĘGU NA PUSTY ZAMEK');

                    return $this->firstBlock($enemies, $mArmy, $castlesAndFields);
                } else {
                    //idź do zamku
                    $this->_l->log('WRÓG MA ZASIĘG NA PUSTY ZAMEK - IDŹ DO ZAMKU!');

                    Cli_Model_Army::updateArmyPosition($this->_playerId, $myEmptyCastle['path'], $castlesAndFields['fields'], $army, $this->_gameId, $this->_db);
                    return $this->endMove($army['armyId'], $myEmptyCastle['currentPosition'], $myEmptyCastle['path']);
                }
            }
        }
    }

    private function endMove($oldArmyId, $position, $path = null, $fightEnemyResults = null, $castleId = null, $ruinId = null)
    {
        if ($position) {
            $armiesIds = Cli_Model_Army::joinArmiesAtPosition($position, $this->_playerId, $this->_gameId, $this->_db);
            $armyId = $armiesIds['armyId'];
        }

        if (!isset($armyId)) {
            $armyId = $oldArmyId;
            $armiesIds = array('deletedIds' => null);
        }

        if ($fightEnemyResults) {
            $attackerArmy = $fightEnemyResults['attackerArmy'];
            $attackerArmy['x'] = $position['x'];
            $attackerArmy['y'] = $position['y'];
            $defenderArmy = $fightEnemyResults['defenderArmy'];
        } else {
            $attackerArmy = Cli_Model_Army::getArmyByArmyIdPlayerId($armyId, $this->_playerId, $this->_gameId, $this->_db);
            $defenderArmy = null;
        }

//        print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4));

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        return array(
            'defenderColor' => $fightEnemyResults['defenderColor'],
            'defenderArmy' => $defenderArmy,
            'attackerColor' => $playersInGameColors[$this->_playerId],
            'attackerArmy' => $attackerArmy,
            'battle' => $fightEnemyResults['battle'],
            'victory' => $fightEnemyResults['victory'],
            'path' => $path,
            'castleId' => $castleId,
            'ruinId' => $ruinId,
            'deletedIds' => $armiesIds['deletedIds'],
            'oldArmyId' => $oldArmyId,
            'action' => 'continue'
        );
    }

    static public function handleHeroResurrection($gameId, $playerId, $db, $gameHandler)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $gold = $mPlayersInGame->getPlayerGold($playerId);

        if ($gold < 100) {
            return;
        }

        $capitals = Zend_Registry::get('capitals');
        $playersInGameColors = Zend_Registry::get('playersInGameColors');
        $color = $playersInGameColors[$playerId];
        $castleId = $capitals[$color];

        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        if (!$mCastlesInGame->isPlayerCastle($castleId, $playerId)) {
            return;
        }

        $mapCastles = Zend_Registry::get('castles');

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $heroId = $mHeroesInGame->getDeadHeroId($playerId);

        if (!$heroId) {
            return;
        }

        $armyId = Cli_Model_Army::heroResurrection($gameId, $heroId, $mapCastles[$castleId]['position'], $playerId, $db);

        if (!$armyId) {
            return;
        }

        $gold -= 100;
        $mPlayersInGame->updatePlayerGold($playerId, $gold);

        $token = array(
            'type' => 'resurrection',
            'data' => array(
                'army' => Cli_Model_Army::getArmyByArmyId($armyId, $gameId, $db),
                'gold' => $gold
            ),
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $gameId);
    }

}

