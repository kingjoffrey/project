var BattleWindow = new function () {
    var castleMesh = null,
        towerMesh = null,
        kill = function (b, r, ii) {
            for (var i in b) {
                break
            }

            if (notSet(b[i])) {
                setTimeout(function () {
                    $('#game').css('display', 'block')
                    GameRenderer.init('game', GameScene)
                    $('#battle').remove()
                    if (castleMesh) {
                        GameScene.remove(castleMesh)
                    }
                    if (towerMesh) {
                        GameScene.remove(towerMesh)
                    }

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
                    if (CommonMe.colorEquals(r.color)) {
                        var soldier = CommonMe.getArmy(r.army.id).getWalkingSoldier(b[i].soldierId)
                        if (isTruthful(soldier)) {
                            CommonMe.upkeepIncrement(-Units.get(soldier.unitId).cost)
                        }
                        soldier = CommonMe.getArmy(r.army.id).getFlyingSoldier(b[i].soldierId)
                        if (isTruthful(soldier)) {
                            CommonMe.upkeepIncrement(-Units.get(soldier.unitId).cost)
                        }
                        soldier = CommonMe.getArmy(r.army.id).getSwimmingSoldier(b[i].soldierId)
                        if (isTruthful(soldier)) {
                            CommonMe.upkeepIncrement(-Units.get(soldier.unitId).cost)
                        }
                    }

                    for (var color in r.defenders) {
                        if (CommonMe.colorEquals(color)) {
                            for (var armyId in r.defenders[color]) {
                                var soldier = CommonMe.getArmy(armyId).getWalkingSoldier(b[i].soldierId)
                                if (isTruthful(soldier)) {
                                    CommonMe.upkeepIncrement(-Units.get(soldier.unitId).cost)
                                }
                                soldier = CommonMe.getArmy(r.army.id).getFlyingSoldier(b[i].soldierId)
                                if (isTruthful(soldier)) {
                                    CommonMe.upkeepIncrement(-Units.get(soldier.unitId).cost)
                                }
                                soldier = CommonMe.getArmy(r.army.id).getSwimmingSoldier(b[i].soldierId)
                                if (isTruthful(soldier)) {
                                    CommonMe.upkeepIncrement(-Units.get(soldier.unitId).cost)
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
        $('#game').css('display', 'none')
        $('body').append($('<div>').attr('id', 'battle'))
        GameRenderer.init('battle', BattleScene)

        var killed = {},
            attackArmy = Players.get(r.color).getArmies().get(r.army.id),
            attackBgColor = attackArmy.getBackgroundColor(),
            unitNumber = 0

        for (var soldierId in r.battle.attack.walk) {
            var mesh = BattleModels.addUnit('attack', unitNumber, attackBgColor, Unit.getName(attackArmy.getWalkingSoldier(soldierId).unitId), BattleScene)
            if (r.battle.attack.walk[soldierId]) {
                killed[r.battle.attack.walk[soldierId]] = {
                    'soldierId': soldierId,
                    'mesh': mesh
                }
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
                    }
                    unitNumber++
                }
            }

            if (r.battle.castleId) {
                var castle
                for (var color in Players.toArray()) {
                    if (castle = Players.get(color).getCastles().get(r.battle.castleId)) {
                        castleMesh = BattleModels.addCastle(castle, color, BattleScene)
                    }
                }
            }

            if (r.battle.towerId) {
                var tower
                for (var color in Players.toArray()) {
                    if (Players.get(color).getTowers().get(r.battle.towerId)) {
                        towerMesh = BattleModels.addTower(color, BattleScene)
                    }
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

    var configuration = function (type) {
        var sequenceNumber = $('<div>'),
            sequenceImage = $('<div>').attr('id', 'sortable'),
            i = 0

        for (k in CommonMe.getBattleSequence(type)) {
            var unitId = CommonMe.getBattleSequence(type)[k],
                unit = Units.get(unitId)
            if (unit.canFly) {
                continue
            }
            if (unit.canSwim) {
                continue
            }
            i++
            if (isSet(unit.name_lang)) {
                var name = unit.name_lang
            } else {
                var name = unit.name
            }
            sequenceNumber
                .append($('<div>').html(i).addClass('battleNumber'))
            sequenceImage
                .append(
                    $('<div>')
                        .append($('<img>').attr({
                            src: Unit.getImage(unitId, CommonMe.getColor()),
                            id: unitId,
                            alt: name
                        }))
                        .addClass('battleUnit')
                )
        }

        return sequenceNumber.add(sequenceImage)
    }
    this.attack = function () {
        var div = $('<div>')
            .append($('<div>').html(translations.changeBattleAttackSequenceByMovingUnits))
            .append(configuration('attack'))

        var id = Message.show(translations.battleConfiguration, div)
        Message.ok(id, WebSocketSend.battleAttack)
        Message.cancel(id)

        $("#sortable").sortable()
        $("#sortable").disableSelection()

    }
    this.defence = function () {
        var div = $('<div>')
            .append($('<div>').html(translations.changeBattleDefenceSequenceByMovingUnits))
            .append(configuration('defense'))

        var id = Message.show(translations.battleConfiguration, div)
        Message.ok(id, WebSocketSend.battleDefence)
        Message.cancel(id)

        $("#sortable").sortable()
        $("#sortable").disableSelection()
    }
}