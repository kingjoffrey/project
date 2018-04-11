var Move = new function () {
    var stepTime = 200,
        player,
        army,
        handleUpkeep = function (defenders) {
            for (var color in defenders) {
                if (Me.colorEquals(color)) {
                    var defenderArmies = Me.getArmies(),
                        upkeep = 0

                    for (var armyId in defenders[color]) {
                        var battleArmy = defenders[color][armyId],
                            defenderArmy = defenderArmies.get(armyId)

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
                    Me.upkeepIncrement(upkeep)
                    break
                }
            }
        },
        handleTowerId = function (id, color) {
            if (id) {
                var field = Fields.get(army.getX(), army.getY()),
                    towerId = field.getTowerId(),
                    oldTowerColor = field.getTowerColor(),
                    towers = Players.get(oldTowerColor).getTowers(),
                    tower = towers.get(towerId)
                if (id != towerId) {
                    console.log('błąd                           !!!')
                }
                Players.get(color).getTowers().add(towerId, tower)
                towers.delete(towerId)

                if (Me.colorEquals(color)) {
                    Me.incomeIncrement(5)
                } else if (Me.colorEquals(oldTowerColor)) {
                    Me.incomeIncrement(-5)
                }
            }

        },
        handleCastleId = function (id, color) {
            if (id) {
                var oldCastleColor = Fields.get(army.getX(), army.getY()).getCastleColor()
                var oldCastles = Players.get(oldCastleColor).getCastles(),
                    newCastles = Players.get(color).getCastles()

                newCastles.add(id, oldCastles.get(id))
                oldCastles.delete(id)

                if (oldCastleColor != 'neutral') {
                    var castle = newCastles.get(id),
                        defense = castle.getDefense()
                    if (defense > 1) {
                        defense--
                        castle.setDefense(defense)
                    }
                }
            }
            if (Me.colorEquals(color)) {
                Me.incomeIncrement(Me.getCastle(id).getIncome())
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
        army = player.getArmies().get(r.army.id)

        Fields.get(army.getX(), army.getY()).removeArmyId(r.army.id)

        switch (army.getMovementType()) {
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
                GameModels.setArmyPosition(army.getMesh(), r.path[step].x, r.path[step].y)
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
                    }, stepTime)
                })
            } else {
                Move.end(r, ii)
            }
        }
    }
    this.end = function (r, ii) {
        if (r.battle) {
            handleUpkeep(r.battle.defenders)

            if (r.battle.victory) {
                army.update(r.army)

                handleTowerId(r.battle.towerId, r.color)
                handleCastleId(r.battle.castleId, r.color)
                handleDefendersLost(r.battle.defenders)

                if (Me.colorEquals(r.color)) {
                    if (r.battle.castleId) {
                        CastleWindow.show(Me.getCastle(r.battle.castleId))
                    } else if (Me.getArmy(army.getArmyId()).getMoves()) {
                        Me.selectArmy(army.getArmyId())
                    }
                    GameGui.unlock()
                }
            } else {
                Players.get(r.color).getArmies().destroy(army.getArmyId())

                handleDefendersWon(r.battle.defenders)

                if (Me.colorEquals(r.color)) {
                    Message.simple('Battle', 'LOST')
                    GameGui.unlock()
                }
            }
            Execute.setExecuting(0)
        } else if (Me.colorEquals(r.color)) {
            army.update(r.army)

            if (Unit.countNumberOfUnits(army)) {
                if (army.getMoves() > 0) {
                    Me.selectArmy(r.army.id)
                }
            }
            GameGui.unlock()
            Execute.setExecuting(0)
        } else {
            army.update(r.army)

            Execute.setExecuting(0)
        }

        for (var i in r.deletedIds) {
            Players.get(r.color).getArmies().destroy(r.deletedIds[i])
        }

        GameModels.clearMoveCircles()
    }
}
