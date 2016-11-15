var BattleWindow = new function () {
    var kill = function (b, r, ii) {
        console.log('start kill')
        for (var i in b) {
            break
        }

        if (notSet(b[i])) {
            // if (!Players.get(r.color).isComputer()) {
            //     $('.close').fadeIn(100)
            // }

            console.log('NOT set killed')

            $('#game').css('display', 'block')
            GameRenderer.init('game', GameScene)
            $('#battle').remove()

            Move.end(r, ii)
            return
        }

        if (isSet(b[i].soldierId)) {
            // var unitElement = $('#unit' + b[i].soldierId)
            // if (!unitElement.length) {
            //     Move.end(r, ii)
            // }
            // unitElement.append($('<div>').addClass('killed'))

            if (!Players.get(r.color).isComputer()) {
                setTimeout(function () {
                    Sound.play('error');
                }, 500)
            }

            // $('#unit' + b[i].soldierId + ' .killed').fadeIn(1000, function () {
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
            // })
        } else if (isSet(b[i].heroId)) {
            // var heroElement = $('#hero' + b[i].heroId)
            // if (!heroElement.length) {
            //     Move.end(r, ii)
            // }
            // heroElement.append($('<div>').addClass('killed'));

            if (!Players.get(r.color).isComputer()) {
                setTimeout(function () {
                    Sound.play('error');
                }, 500)
            }

            // $('#hero' + b[i].heroId + ' .killed').fadeIn(1000, function () {
            setTimeout(function () {
                BattleScene.remove(b[i].mesh)
                delete b[i]
                kill(b, r, ii)
            }, 1000)
            // });
        }
        console.log('end kill')
    }

    this.battle = function (r, ii) {
        console.log('START battle')

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

            //if (r.battle.castleId) {
            //    defenseLayout.append(
            //        $('<div>')
            //            .addClass('castle')
            //            .css({
            //                position: 'static',
            //                background: 'url(/img/game/castles/' + color + '.png) center center no-repeat',
            //                margin: '0 auto'
            //            })
            //    )
            //}
            //
            //if (r.battle.towerId) {
            //    defenseLayout.append(
            //        $('<div>')
            //            .addClass('tower')
            //            .css({
            //                position: 'static',
            //                background: 'url(/img/game/towers/' + color + '.png) center center no-repeat',
            //                margin: '0 auto'
            //            })
            //    )
            //}


        }

        // $('.message .close').css('display', 'none')

        // if (killed) {
        if (Players.get(r.color).isComputer()) {
            kill(killed, r, ii);
        } else {
            setTimeout(function () {
                kill(killed, r, ii);
            }, 2500);
        }
        // }
        console.log('END battle')
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