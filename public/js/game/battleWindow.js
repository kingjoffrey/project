var BattleWindow = new function () {
    var castleMesh = null,
        towerMesh = null,
        winners = [],
        kill = function (b, r, ii) {
            for (var i in b) {
                break
            }

            if (notSet(b[i])) {
                setTimeout(function () {
                    $('#game').show()
                    $('#battle').hide()
                    GameRenderer.init('game', GameScene)

                    if (castleMesh) {
                        BattleScene.remove(castleMesh)
                    }
                    if (towerMesh) {
                        BattleScene.remove(towerMesh)
                    }

                    for (var i in winners) {
                        BattleScene.remove(winners[i])
                    }
                    console.log(r)
                    Move.end(r, ii)
                }, 500)
                return
            }

            if (isSet(b[i].soldierId)) {
                if (!Players.get(r.color).isComputer()) {
                    setTimeout(function () {
                        Sound.play('error');
                    }, 500)
                }

                setTimeout(function () {
                    if (Me.colorEquals(r.color)) {
                        var soldier = Me.getArmy(r.army.id).getWalkingSoldier(b[i].soldierId)
                        if (isTruthful(soldier)) {
                            Me.upkeepIncrement(-Units.get(soldier.unitId).cost)
                        }
                        soldier = Me.getArmy(r.army.id).getFlyingSoldier(b[i].soldierId)
                        if (isTruthful(soldier)) {
                            Me.upkeepIncrement(-Units.get(soldier.unitId).cost)
                        }
                        soldier = Me.getArmy(r.army.id).getSwimmingSoldier(b[i].soldierId)
                        if (isTruthful(soldier)) {
                            Me.upkeepIncrement(-Units.get(soldier.unitId).cost)
                        }
                    }

                    for (var color in r.defenders) {
                        if (Me.colorEquals(color)) {
                            for (var armyId in r.defenders[color]) {
                                var soldier = Me.getArmy(armyId).getWalkingSoldier(b[i].soldierId)
                                if (isTruthful(soldier)) {
                                    Me.upkeepIncrement(-Units.get(soldier.unitId).cost)
                                }
                                soldier = Me.getArmy(r.army.id).getFlyingSoldier(b[i].soldierId)
                                if (isTruthful(soldier)) {
                                    Me.upkeepIncrement(-Units.get(soldier.unitId).cost)
                                }
                                soldier = Me.getArmy(r.army.id).getSwimmingSoldier(b[i].soldierId)
                                if (isTruthful(soldier)) {
                                    Me.upkeepIncrement(-Units.get(soldier.unitId).cost)
                                }
                            }
                            break;
                        }
                    }
                    BattleScene.remove(b[i].mesh)
                    delete b[i];
                    kill(b, r, ii);
                }, 1000)
            } else if (isSet(b[i].heroId)) {
                if (!Players.get(r.color).isComputer()) {
                    setTimeout(function () {
                        Sound.play('error');
                    }, 500)
                }

                setTimeout(function () {
                    BattleScene.remove(b[i].mesh)
                    delete b[i]
                    kill(b, r, ii)
                }, 1000)
            }
        }

    this.battle = function (r, ii) {
        $('#game').hide()
        $('#battle').show()

        GameRenderer.init('battle', BattleScene)

        var killed = {},
            attackArmy = Players.get(r.color).getArmies().get(r.army.id),
            attackBgColor = attackArmy.getBackgroundColor(),
            unitNumber = 0

        winners = []

        for (var soldierId in r.battle.attack.walk) {
            var mesh = BattleModels.addUnit('attack', unitNumber, attackBgColor, Unit.getName(attackArmy.getWalkingSoldier(soldierId).unitId), BattleScene)
            if (r.battle.attack.walk[soldierId]) {
                killed[r.battle.attack.walk[soldierId]] = {
                    'soldierId': soldierId,
                    'mesh': mesh
                }
            } else {
                winners.push(mesh)
            }
            unitNumber++
        }
        for (var soldierId in r.battle.attack.swim) {
            var mesh = BattleModels.addUnit('attack', unitNumber, attackBgColor, Unit.getName(attackArmy.getSwimmingSoldier(soldierId).unitId), BattleScene)
            if (r.battle.attack.swim[soldierId]) {
                killed[r.battle.attack.swim[soldierId]] = {
                    'soldierId': soldierId,
                    'mesh': mesh
                }
            } else {
                winners.push(mesh)
            }
            unitNumber++
        }
        for (var soldierId in r.battle.attack.fly) {
            var mesh = BattleModels.addUnit('attack', unitNumber, attackBgColor, Unit.getName(attackArmy.getFlyingSoldier(soldierId).unitId), BattleScene)
            if (r.battle.attack.fly[soldierId]) {
                killed[r.battle.attack.fly[soldierId]] = {
                    'soldierId': soldierId,
                    'mesh': mesh
                }
            } else {
                winners.push(mesh)
            }
            unitNumber++
        }
        for (var heroId in r.battle.attack.hero) {
            var mesh = BattleModels.addHero('attack', unitNumber, attackBgColor, BattleScene)
            if (r.battle.attack.hero[heroId]) {
                killed[r.battle.attack.hero[heroId]] = {
                    'heroId': heroId,
                    'mesh': mesh
                }
            } else {
                winners.push(mesh)
            }
            unitNumber++
        }

        unitNumber = 0

        for (var color in r.battle.defenders) {
            for (var armyId in r.battle.defenders[color]) {
                var defenseArmy = Players.get(color).getArmies().get(armyId),
                    defenseBgArmy = defenseArmy.getBackgroundColor()

                for (var soldierId in r.battle.defenders[color][armyId].walk) {
                    if (color == 'neutral') {
                        var unitId = Game.getFirstUnitId()
                    } else {
                        var unitId = defenseArmy.getWalkingSoldier(soldierId).unitId
                    }
                    var mesh = BattleModels.addUnit('defense', unitNumber, defenseBgArmy, Unit.getName(unitId), BattleScene)
                    if (r.battle.defenders[color][armyId].walk[soldierId]) {
                        killed[r.battle.defenders[color][armyId].walk[soldierId]] = {
                            'soldierId': soldierId,
                            'mesh': mesh
                        }
                    } else {
                        winners.push(mesh)
                    }
                    unitNumber++
                }
                for (var soldierId in r.battle.defenders[color][armyId].swim) {
                    var mesh = BattleModels.addUnit('defense', unitNumber, defenseBgArmy, Unit.getName(defenseArmy.getSwimmingSoldier(soldierId).unitId), BattleScene)
                    if (r.battle.defenders[color][armyId].swim[soldierId]) {
                        killed[r.battle.defenders[color][armyId].swim[soldierId]] = {
                            'soldierId': soldierId,
                            'mesh': mesh
                        }
                    } else {
                        winners.push(mesh)
                    }
                    unitNumber++
                }
                for (var soldierId in r.battle.defenders[color][armyId].fly) {
                    var mesh = BattleModels.addUnit('defense', unitNumber, defenseBgArmy, Unit.getName(defenseArmy.getFlyingSoldier(soldierId).unitId), BattleScene)
                    if (r.battle.defenders[color][armyId].fly[soldierId]) {
                        killed[r.battle.defenders[color][armyId].fly[soldierId]] = {
                            'soldierId': soldierId,
                            'mesh': mesh
                        }
                    } else {
                        winners.push(mesh)
                    }
                    unitNumber++
                }
                for (var heroId in r.battle.defenders[color][armyId].hero) {
                    var mesh = BattleModels.addHero('defense', unitNumber, defenseBgArmy, BattleScene)
                    if (r.battle.defenders[color][armyId].hero[heroId]) {
                        killed[r.battle.defenders[color][armyId].hero[heroId]] = {
                            'heroId': heroId,
                            'mesh': mesh
                        }
                    } else {
                        winners.push(mesh)
                    }
                    unitNumber++
                }
            }

            if (r.battle.castleId) {
                if (Players.get(color).getCastles().has(r.battle.castleId)) {
                    var castle = Players.get(color).getCastles().get(r.battle.castleId)
                    castleMesh = BattleModels.addCastle(castle.toArray(), Players.get(color).getBackgroundColor(), BattleScene)
                }
            }

            if (r.battle.towerId) {
                if (Players.get(color).getTowers().has(r.battle.towerId)) {
                    var tower = Players.get(color).getTowers().get(r.battle.towerId)
                    towerMesh = BattleModels.addTower(Players.get(color).getBackgroundColor(), BattleScene)
                }
            }
        }

        if (Players.get(r.color).isComputer()) {
            kill(killed, r, ii);
        } else {
            setTimeout(function () {
                kill(killed, r, ii);
            }, 2500);
        }
    }
}