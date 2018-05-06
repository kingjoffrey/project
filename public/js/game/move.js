var Move = new function () {
    var stepTime = 50,
        battleTime = 1000,
        player,
        oldArmy,
        handleMyRuins = function () {
            var field = Fields.get(oldArmy.getX(), oldArmy.getY()),
                ruinId = field.getRuinId()

            if (!ruinId) {
                return
            }

            if (Ruins.get(ruinId).isEmpty()) {
                return
            }

            if (!Ruins.get(ruinId).isRandom()) {
                return
            }

            for (var i in oldArmy.getHeroes()) {
                if (oldArmy.getHero(i).movesLeft > 0) {
                    WebSocketSendGame.ruin()
                    return
                }
            }
        },
        handleMyUpkeepAsDefender = function (defenders) {
            for (var color in defenders) {
                if (Me.colorEquals(color)) {
                    var upkeep = 0

                    for (var armyId in defenders[color]) {
                        var battleArmy = defenders[color][armyId],
                            defenderArmy = Me.getArmy(armyId)

                        for (var soldierId in battleArmy.walk) {
                            if (battleArmy.walk[soldierId]) {
                                upkeep -= Units.get(defenderArmy.getWalkingSoldier(soldierId).unitId).cost
                            }
                        }
                        for (var soldierId in battleArmy.swim) {
                            if (battleArmy.swim[soldierId]) {
                                upkeep -= Units.get(defenderArmy.getSwimmingSoldier(soldierId).unitId).cost
                            }
                        }
                        for (var soldierId in battleArmy.fly) {
                            if (battleArmy.fly[soldierId]) {
                                upkeep -= Units.get(defenderArmy.getFlyingSoldier(soldierId).unitId).cost
                            }
                        }
                    }

                    if (upkeep) {
                        Me.upkeepIncrement(upkeep)
                    }

                    return
                }
            }
        },
        handleMyUpkeepAsAttacker = function (army, battleArmy, color) {
            if (!Me.colorEquals(color)) {
                return
            }
            var upkeep = 0

            for (var soldierId in battleArmy.walk) {
                if (battleArmy.walk[soldierId]) {
                    upkeep -= Units.get(army.getWalkingSoldier(soldierId).unitId).cost
                }
            }
            for (var soldierId in battleArmy.swim) {
                if (battleArmy.swim[soldierId]) {
                    upkeep -= Units.get(army.getSwimmingSoldier(soldierId).unitId).cost
                }
            }
            for (var soldierId in battleArmy.fly) {
                if (battleArmy.fly[soldierId]) {
                    upkeep -= Units.get(army.getFlyingSoldier(soldierId).unitId).cost
                }
            }

            if (upkeep) {
                Me.upkeepIncrement(upkeep)
            }
        },
        handleTowerId = function (army, id, color) {
            if (id) {
                var field = Fields.get(army.x, army.y),
                    towerId = field.getTowerId(),
                    oldTowerColor = field.getTowerColor(),
                    towers = Players.get(oldTowerColor).getTowers(),
                    tower = towers.get(towerId)
                if (id != towerId) {
                    console.log('błąd                           !!!')
                }
                Players.get(color).getTowers().add(towerId, tower)
                towers.delete(towerId)

                if (Me.colorEquals(color)) { // zdobyłem wieże
                    Me.incomeIncrement(5)
                } else if (Me.colorEquals(oldTowerColor)) { // straciłem wieże
                    Me.incomeIncrement(-5)
                }
            }

        },
        handleCastleId = function (army, id, color) {
            if (id) {
                var oldCastleColor = Fields.get(army.x, army.y).getCastleColor()
                var oldCastles = Players.get(oldCastleColor).getCastles(),
                    newCastles = Players.get(color).getCastles()

                newCastles.add(id, oldCastles.get(id))

                if (oldCastleColor != 'neutral') {
                    var castle = newCastles.get(id),
                        defense = castle.getDefense()
                    if (defense > 1) {
                        defense--
                        castle.setDefense(defense)
                    }
                }

                if (Me.colorEquals(color)) { // zdobyłem zamek
                    Me.incomeIncrement(newCastles.get(id).getIncome())
                } else if (Me.colorEquals(oldCastleColor)) { // straciłem zamek
                    Me.incomeIncrement(-oldCastles.get(id).getIncome())
                }

                oldCastles.delete(id)
            }
        },
        handleDefendersLost = function (defenders) {
            for (var color in defenders) {
                var armies = Players.get(color).getArmies()
                for (var armyId in defenders[color]) {
                    armies.destroy(armyId)
                }
            }
        },
        handleDefendersWon = function (defenders) {
            for (var color in defenders) {
                if (color == 'neutral') {
                    return
                }

                var armies = Players.get(color).getArmies()

                for (var armyId in defenders[color]) {
                    var battleArmy = defenders[color][armyId],
                        army = armies.get(armyId)

                    for (var soldierId in battleArmy.walk) {
                        if (battleArmy.walk[soldierId]) {
                            army.deleteWalkingSoldier(soldierId)
                        }
                    }
                    for (var soldierId in battleArmy.swim) {
                        if (battleArmy.swim[soldierId]) {
                            army.deleteSwimmingSoldier(soldierId)
                        }
                    }
                    for (var soldierId in battleArmy.fly) {
                        if (battleArmy.fly[soldierId]) {
                            army.deleteFlyingSoldier(soldierId)
                        }
                    }
                    for (var heroId in battleArmy.hero) {
                        if (battleArmy.hero[heroId]) {
                            army.deleteHero(heroId)
                        }
                    }

                    if (Unit.countNumberOfUnits(army.toArray())) {
                        army.update(army.toArray())
                    } else {
                        armies.destroy(armyId)
                    }
                }
            }
        }

    this.start = function (r, ii) {
        if (notSet(r.path)) {
            Execute.setExecuting(0)
            if (Me.colorEquals(r.color)) {
                GameGui.unlock()
                GameModels.clearMoveCircles()
                Message.simple(translations.army, translations.noMoreMoves)
            }
            return
        }
        player = Players.get(r.color)
        oldArmy = player.getArmies().get(r.army.id)

        Fields.get(oldArmy.getX(), oldArmy.getY()).removeArmyId(r.army.id)

        switch (oldArmy.getMovementType()) {
            case 'fly':
                Sound.play('fly');
                break;
            case 'swim':
                Sound.play('swim');
                break;
            default:
                Sound.play('walk');
                break;
        }

        if (Turn.isMy() || (!player.isComputer() || GameGui.getShow())) {
            Message.remove()
        }

        // GameRenderer.shadowsOn()

        if (player.isComputer() && !GameGui.getShow()) {
            stepLoop(r, ii)
        } else {
            Move.startStepLoop(r, ii)
        }
    }
    this.startStepLoop = function (r, ii) {
        stepLoop(r, ii)
    }
    var stepLoop = function (r, ii) {
        for (var step in r.path) {
            if (notSet(r.path[step].c)) {
                continue
            }
            break
        }

        if (isSet(r.path[step].c)) {
            if (!player.isComputer() || GameGui.getShow()) {
                GameModels.setArmyPosition(oldArmy.getMesh(), r.path[step].x, r.path[step].y)
                delete r.path[step].c
                setTimeout(function () {
                    stepLoop(r, ii)
                }, stepTime)
            } else {
                delete r.path[step].c;
                stepLoop(r, ii);
            }
        } else {
            if (isTruthful(r.battle) && (!player.isComputer() || GameGui.getShow())) {
                Sound.play('fight')
                GameScene.centerOn(r.path[step].x, r.path[step].y, function () {
                    setTimeout(function () {
                        Move.end(r, ii)
                    }, battleTime)
                })
            } else {
                Move.end(r, ii)
            }
        }
    }
    this.end = function (r, ii) {
        if (r.battle) {                                                                        // "A" była walka
            handleMyUpkeepAsDefender(r.battle.defenders)
            handleMyUpkeepAsAttacker(oldArmy, r.battle.attack, r.color)

            if (r.battle.victory) {                                                        // "Aa" ATAKUJĄCY wygrał
                handleTowerId(r.army, r.battle.towerId, r.color)
                handleCastleId(r.army, r.battle.castleId, r.color)
                handleDefendersLost(r.battle.defenders)

                oldArmy.update(r.army)

                if (Me.colorEquals(r.color)) {                                                 // wygrałem
                    if (r.battle.castleId) {
                        setTimeout(function () {
                            CastleWindow.show(Me.getCastle(r.battle.castleId))
                        }, 500)
                    } else if (Me.getArmy(oldArmy.getArmyId()).getMoves()) {
                        Me.selectArmy(oldArmy.getArmyId())
                        GameScene.centerOn(Me.getSelectedArmy().getX(), Me.getSelectedArmy().getY())
                    }
                    GameGui.unlock()
                }
            } else {                                                                       // "Ab" ATAKUJĄCY przegrał
                handleDefendersWon(r.battle.defenders)

                if (Me.colorEquals(r.color)) { // przegrałem
                    Message.simple('Battle', 'LOST')
                    GameGui.unlock()
                }

                Players.get(r.color).getArmies().destroy(oldArmy.getArmyId())
            }
            Execute.setExecuting(0)
        } else {                                                                            // "B" nie było walki
            oldArmy.update(r.army)
            if (Me.colorEquals(r.color)) {                                                  // mój ruch bez walki
                if (oldArmy.getMoves() > 0) {
                    Me.selectArmy(r.army.id)
                    handleMyRuins()
                }
                GameGui.unlock()
            }
            Execute.setExecuting(0)
        }

        for (var i in r.deletedIds) {
            Players.get(r.color).getArmies().destroy(r.deletedIds[i])
        }

        GameModels.clearMoveCircles()
    }
}
