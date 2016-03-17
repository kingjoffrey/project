var Move = new function () {
    var stepTime = 200,
        player,
        army

    this.start = function (r, ii) {
        if (notSet(r.path)) {
            Execute.setExecuting(0)
            if (CommonMe.colorEquals(r.color)) {
                Gui.unlock()
                Message.simple(translations.army, translations.noMoreMoves)
            }
            return
        }
        player = Players.get(r.color)
        army = player.getArmies().get(r.army.id)

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

        if (Turn.isMy() || (!player.isComputer() || Gui.getShow())) {
            Message.remove()
        }

        if (player.isComputer() && !Gui.getShow()) {
            stepLoop(r, ii)
        } else {
            MiniMap.centerOn(r.path[0].x, r.path[0].y, function () {
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
                        left: MiniMap.calculateX(r.path[step].x) + 'px',
                        top: MiniMap.calculateY(r.path[step].y) + 'px'
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
                MiniMap.centerOn(r.path[step].x, r.path[step].y, function () {
                    BattleWindow.battle(r, ii)
                })
            } else {
                Move.end(r, ii)
            }
        }
    }
    this.end = function (r, ii) {
        army.update(r.army)
        //MiniMap.centerOn(army.getX(), army.getY())

        if (r.battle) {
            if (player.isComputer() && !Gui.getShow()) {
                for (var color in r.battle.defenders) {
                    if (CommonMe.colorEquals(color)) {
                        var defenderArmies = CommonMe.getArmies(),
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
                        CommonMe.upkeepIncrement(upkeep)
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
                if (CommonMe.colorEquals(r.color)) {
                    if (r.battle.castleId) {
                        CastleWindow.show(CommonMe.getCastle(r.battle.castleId))
                        CommonMe.incomeIncrement(CommonMe.getCastle(r.battle.castleId).getIncome())
                    } else if (CommonMe.getArmy(army.getArmyId()).getMoves()) {
                        CommonMe.selectArmy(army.getArmyId())
                    }
                    CommonMe.handleHeroButtons()
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
                if (CommonMe.colorEquals(r.color)) {
                    if (Turn.isMy()) {
                        CommonMe.handleHeroButtons()
                    }
                    Gui.unlock()
                }
            }
        } else if (CommonMe.colorEquals(r.color)) {
            if (army.getNumberOfUnits()) {
                if (army.getMoves() > 0) {
                    CommonMe.selectArmy(r.army.id)
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
