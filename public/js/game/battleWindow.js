var BattleWindow = new function () {
    var kill = function (b, r, ii) {
            for (var i in b) {
                break
            }

            if (notSet(b[i])) {
                if (!Players.get(r.color).isComputer()) {
                    $('.close').fadeIn(100)
                }
                Move.end(r, ii)
                return
            }

            if (isSet(b[i].soldierId)) {
                var unitElement = $('#unit' + b[i].soldierId)
                if (!unitElement.length) {
                    Move.end(r, ii)
                }

                unitElement.append($('<div>').addClass('killed'));
                if (!Players.get(r.color).isComputer()) {
                    setTimeout(function () {
                        Sound.play('error');
                    }, 500)
                }
                $('#unit' + b[i].soldierId + ' .killed').fadeIn(1000, function () {
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
                    delete b[i];
                    kill(b, r, ii);
                })
            } else if (isSet(b[i].heroId)) {
                var heroElement = $('#hero' + b[i].heroId)
                if (!heroElement.length) {
                    Move.end(r, ii)
                }

                heroElement.append($('<div>').addClass('killed'));
                if (!Players.get(r.color).isComputer()) {
                    setTimeout(function () {
                        Sound.play('error');
                    }, 500)
                }
                $('#hero' + b[i].heroId + ' .killed').fadeIn(1000, function () {
                    delete b[i];
                    kill(b, r, ii);
                });
            }
        },
        addUnit = function (soldierId, color, unitId, elementId) {
            var scene = new SimpleScene()
            scene.init(40, 40, elementId)
            scene.setCameraPosition(-8, 16)
            scene.initSun(30)
            scene.add(Models.addUnit(4, 4, color, Unit.getName(unitId)))
            scene.render()
            scene.addId('unit' + soldierId)
        },
        addHero = function (color, heroId, elementId) {
            var scene = new SimpleScene()
            scene.init(40, 40, elementId)
            scene.setCameraPosition(-8, 16)
            scene.initSun(30)
            scene.add(Models.addHero(4, 4, color))
            scene.render()
            scene.addId('hero' + heroId)
        }

    this.battle = function (r, ii) {

        var div = $('<div>')
            .append($('<p>').html('&nbsp;'))
            .append(
                $('<div>')
                    .addClass('grass')
                    .append(
                        $('<div>')
                            .append($('<div>').html(Players.get(color).getLongName() + ' (' + translations.defence + ')'))
                            .append($('<div>').addClass('battle defense').attr('id', 'attack'))
                    )
                    .append($('<p>').html('&nbsp;'))
                    .append(
                        $('<div>')
                        .append($('<div>').addClass('battle attack').attr('id', 'attack'))
                        .append($('<div>').html(Players.get(r.color).getLongName() + ' (' + translations.attack + ')'))
                    )
            )

        Message.simple(translations.battle, div);

        var killed = new Array(),
            attackArmy = Players.get(r.color).getArmies().get(r.army.id),
            attackBgColor = attackArmy.getBackgroundColor()

        for (var soldierId in r.battle.attack.walk) {
            if (r.battle.attack.walk[soldierId]) {
                killed[r.battle.attack.walk[soldierId]] = {
                    'soldierId': soldierId
                };
            }
            addUnit(soldierId, attackBgColor, attackArmy.getWalkingSoldier(soldierId).unitId, 'attack')
        }
        for (var soldierId in r.battle.attack.swim) {
            if (r.battle.attack.swim[soldierId]) {
                killed[r.battle.attack.swim[soldierId]] = {
                    'soldierId': soldierId
                };
            }
            addUnit(soldierId, attackBgColor, attackArmy.getSwimmingSoldier(soldierId).unitId, 'attack')
        }
        for (var soldierId in r.battle.attack.fly) {
            if (r.battle.attack.fly[soldierId]) {
                killed[r.battle.attack.fly[soldierId]] = {
                    'soldierId': soldierId
                };
            }
            addUnit(soldierId, attackBgColor, attackArmy.getSwimmingSoldier(soldierId).unitId, 'attack')
        }
        for (var heroId in r.battle.attack.hero) {
            if (r.battle.attack.hero[heroId]) {
                killed[r.battle.attack.hero[heroId]] = {
                    'heroId': heroId
                };
            }
            addHero(attackBgColor, heroId, 'attack')
        }

        for (var color in r.battle.defenders) {
            for (var armyId in r.battle.defenders[color]) {
                var defenseArmy = Players.get(color).getArmies().get(armyId),
                    defenseBgArmy = defenseArmy.getBackgroundColor()

                for (var soldierId in r.battle.defenders[color][armyId].walk) {
                    if (r.battle.defenders[color][armyId].walk[soldierId]) {
                        killed[r.battle.defenders[color][armyId].walk[soldierId]] = {
                            'soldierId': soldierId
                        }
                    }
                    if (color == 'neutral') {
                        var unitId = Game.getFirstUnitId()
                    } else {
                        var unitId = defenseArmy.getWalkingSoldier(soldierId).unitId
                    }
                    addUnit(soldierId, defenseBgArmy, unitId, 'defense')
                }
                for (var soldierId in r.battle.defenders[color][armyId].swim) {
                    if (r.battle.defenders[color][armyId].swim[soldierId]) {
                        killed[r.battle.defenders[color][armyId].swim[soldierId]] = {
                            'soldierId': soldierId
                        }
                    }
                    addUnit(soldierId, defenseBgArmy, defenseArmy.getSwimmingSoldier(soldierId).unitId, 'defense')
                }
                for (var soldierId in r.battle.defenders[color][armyId].fly) {
                    if (r.battle.defenders[color][armyId].fly[soldierId]) {
                        killed[r.battle.defenders[color][armyId].fly[soldierId]] = {
                            'soldierId': soldierId
                        }
                    }
                    addUnit(soldierId, defenseBgArmy, defenseArmy.getFlyingSoldier(soldierId).unitId, 'defense')
                }
                for (var heroId in r.battle.defenders[color][armyId].hero) {
                    if (r.battle.defenders[color][armyId].hero[heroId]) {
                        killed[r.battle.defenders[color][armyId].hero[heroId]] = {
                            'heroId': heroId
                        }
                    }
                    addHero(defenseBgArmy, heroId, 'defense')
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

        $('.message .close').css('display', 'none')

        if (killed) {
            if (Players.get(r.color).isComputer()) {
                kill(killed, r, ii);
            } else {
                setTimeout(function () {
                    kill(killed, r, ii);
                }, 2500);
            }
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