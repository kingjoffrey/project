var Move = new function () {
    var stepTime = 200,
        player,
        army

    this.start = function (r, ii) {
        if (notSet(r.path)) {
            WebSocketSend.setExecuting(0)
            if (Me.colorEquals(r.color)) {
                Gui.unlock()
                Message.simple(translations.army, translations.noMoreMoves)
            }
            return
        }
        player = Players.get(r.color)
        army = player.getArmies().get(r.army.id)

        switch (army.getMovementType()) {
            case 'flying':
                Sound.play('fly');
                break;
            case 'swimming':
                Sound.play('swim');
                break;
            default:
                Sound.play('walk');
                break;
        }

        if (Turn.isMy() || (!player.isComputer() || Gui.getShow())) {
            Message.remove()
        }

        if (player.isComputer() && !Gui.getShow()) {
            stepLoop(r, ii)
        } else {
            Zoom.getLens().setcenter(r.path[0].x, r.path[0].y, function () {
                Move.startStepLoop(r, ii)
            })
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
            //console.log(step)
            //console.log(r.path[step])
            if (!player.isComputer() || Gui.getShow()) {
                //zoomer.setCenterIfOutOfScreen(r.path[step].x * 40, r.path[step].y * 40);

                $('#' + army.getArmyId() + '.a')
                    .animate({
                        left: Zoom.calculateMiniMapX(r.path[step].x) + 'px',
                        top: Zoom.calculateMiniMapY(r.path[step].y) + 'px'
                    }, stepTime, function () {
                        if (typeof r.path[step] == 'undefined') {
                            console.log('step: ' + step)
                            console.log('path: ' + r.path)
                            throw('error20150224')
                        }
                        Models.setArmyPosition(army.getMesh(), r.path[step].x, r.path[step].y)
                        delete r.path[step].c
                        stepLoop(r, ii);
                    })
            } else {
                delete r.path[step].c;
                stepLoop(r, ii);
            }
        } else {
            if (isTruthful(r.battle) && (!player.isComputer() || Gui.getShow())) {
                Sound.play('fight')
                Zoom.getLens().setcenter(r.path[step].x, r.path[step].y, function () {
                    BattleWindow.battle(r, ii)
                })
            } else {
                Move.end(r, ii)
            }
        }
    }
    this.end = function (r, ii) {
        army.update(r.army)
        if (player.isComputer() && !Gui.getShow()) {
            army.setPosition(army.getX(), army.getY())
        }
        //Zoom.getLens().setcenter(army.getX(), army.getY())

        if (r.battle) {
            if (player.isComputer() && !Gui.getShow()) {
                for (var color in r.battle.defenders) {
                    if (Me.colorEquals(color)) {
                        var defenderArmies = Me.getArmies(),
                            upkeep = 0

                        for (var armyId in r.battle.defenders[color]) {
                            var battleArmy = r.battle.defenders[color][armyId],
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
            }
            if (r.battle.victory) {
                for (var color in r.battle.defenders) {
                    for (var armyId in r.battle.defenders[color]) {
                        Players.get(color).getArmies().destroy(armyId)
                    }
                }
                if (r.battle.towerId) {
                    var field = Fields.get(army.getX(), army.getY()),
                        towerId = field.getTowerId(),
                        towerColor = field.getTowerColor(),
                        towers = Players.get(towerColor).getTowers(),
                        tower = towers.get(towerId)
                    if (r.battle.towerId != towerId) {
                        console.log('błąd                           !!!')
                    }
                    Players.get(r.color).getTowers().add(towerId, tower)
                    towers.delete(towerId)
                }
                if (r.battle.castleId) {
                    var castleColor = Fields.get(army.getX(), army.getY()).getCastleColor(),
                        oldCastles = Players.get(castleColor).getCastles(),
                        newCastles = Players.get(r.color).getCastles()

                    newCastles.add(r.battle.castleId, oldCastles.get(r.battle.castleId))
                    oldCastles.delete(r.battle.castleId)

                    if (castleColor != 'neutral') {
                        var castle = newCastles.get(r.battle.castleId),
                            defense = castle.getDefense()
                        if (defense > 1) {
                            defense--
                            castle.setDefense(defense)
                        }
                    }
                }
                if (Me.colorEquals(r.color)) {
                    if (r.battle.castleId) {
                        CastleWindow.show(Me.getCastle(r.battle.castleId))
                        Me.incomeIncrement(Me.getCastle(r.battle.castleId).getIncome())
                    } else if (Me.getArmy(army.getArmyId()).getMoves()) {
                        Me.selectArmy(army.getArmyId())
                    }
                    Me.handleHeroButtons()
                    Gui.unlock()
                }
            } else {
                Players.get(r.color).getArmies().destroy(army.getArmyId())
                for (var color in r.battle.defenders) {
                    if (color == 'neutral') {
                        break
                    }

                    var defenderArmies = Players.get(color).getArmies()
                    for (var armyId in r.battle.defenders[color]) {
                        var battleArmy = r.battle.defenders[color][armyId],
                            defenderArmy = defenderArmies.get(armyId)

                        for (var soldierId in battleArmy.walk) {
                            if (battleArmy.walk[soldierId]) {
                                defenderArmy.deleteWalkingSoldier(soldierId)
                            }
                        }
                        for (var soldierId in battleArmy.swim) {
                            if (battleArmy.swim[soldierId]) {
                                defenderArmy.deleteSwimmingSoldier(soldierId)
                            }
                        }
                        for (var soldierId in battleArmy.fly) {
                            if (battleArmy.fly[soldierId]) {
                                defenderArmy.deleteFlyingSoldier(soldierId)
                            }
                        }
                        for (var heroId in battleArmy.hero) {
                            if (battleArmy.hero[heroId]) {
                                defenderArmy.deleteHero(heroId)
                            }
                        }
                        defenderArmy.setNumberOfUnits(defenderArmy.toArray())
                        if (defenderArmy.getNumberOfUnits()) {
                            defenderArmy.update(defenderArmy)
                        } else {
                            defenderArmies.destroy(armyId)
                        }
                    }
                }
                if (Me.colorEquals(r.color)) {
                    if (Turn.isMy()) {
                        Me.handleHeroButtons()
                    }
                    Gui.unlock()
                }
            }
        } else if (Me.colorEquals(r.color)) {
            if (army.getNumberOfUnits()) {
                if (army.getMoves() > 0) {
                    Me.selectArmy(r.army.id)
                }
            }
            Gui.unlock()
        }

        for (var i in r.deletedIds) {
            Players.get(r.color).getArmies().destroy(r.deletedIds[i])
        }

        Execute.setExecuting(0)
    }
}
