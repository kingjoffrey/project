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
    }

    this.battle = function (r, ii) {
        var killed = new Array(),
            attack = $('<div>').addClass('battle attack');

        for (var soldierId in r.battle.attack.walk) {
            if (r.battle.attack.walk[soldierId]) {
                killed[r.battle.attack.walk[soldierId]] = {
                    'soldierId': soldierId
                };
            }
            attack.append(
                $('<div>')
                    .attr('id', 'unit' + soldierId)
                    .css('background', 'url(' + Unit.getImage(Players.get(r.color).getArmies().get(r.army.id).getWalkingSoldier(soldierId).unitId, r.color) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }
        for (var soldierId in r.battle.attack.swim) {
            if (r.battle.attack.swim[soldierId]) {
                killed[r.battle.attack.swim[soldierId]] = {
                    'soldierId': soldierId
                };
            }
            attack.append(
                $('<div>')
                    .attr('id', 'unit' + soldierId)
                    .css('background', 'url(' + Unit.getImage(Players.get(r.color).getArmies().get(r.army.id).getSwimmingSoldier(soldierId).unitId, r.color) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }
        for (var soldierId in r.battle.attack.fly) {
            if (r.battle.attack.fly[soldierId]) {
                killed[r.battle.attack.fly[soldierId]] = {
                    'soldierId': soldierId
                };
            }
            attack.append(
                $('<div>')
                    .attr('id', 'unit' + soldierId)
                    .css('background', 'url(' + Unit.getImage(Players.get(r.color).getArmies().get(r.army.id).getFlyingSoldier(soldierId).unitId, r.color) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }
        for (var heroId in r.battle.attack.hero) {
            if (r.battle.attack.hero[heroId]) {
                killed[r.battle.attack.hero[heroId]] = {
                    'heroId': heroId
                };
            }
            attack.append(
                $('<div>')
                    .attr('id', 'hero' + heroId)
                    .css('background', 'url(' + Hero.getImage(r.color) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }

        var attackLayout = $('<div>')
            .append(attack)
            .append($('<div>').html(Players.get(r.color).getLongName() + ' (' + translations.attack + ')'))

        var defense = $('<div>').addClass('battle defense');
        var defenseLayout = $('<div>')

        for (var color in r.battle.defenders) {
            for (var armyId in r.battle.defenders[color]) {
                for (var soldierId in r.battle.defenders[color][armyId].walk) {
                    if (r.battle.defenders[color][armyId].walk[soldierId]) {
                        killed[r.battle.defenders[color][armyId].walk[soldierId]] = {
                            'soldierId': soldierId
                        };
                    }
                    if (color == 'neutral') {
                        var unitId = Game.getFirstUnitId()
                    } else {
                        var unitId = Players.get(color).getArmies().get(armyId).getWalkingSoldier(soldierId).unitId
                    }
                    defense.append(
                        $('<div>')
                            .attr('id', 'unit' + soldierId)
                            .css('background', 'url(' + Unit.getImage(unitId, color) + ') no-repeat')
                            .addClass('battleUnit')
                    );
                }
                for (var soldierId in r.battle.defenders[color][armyId].swim) {
                    if (r.battle.defenders[color][armyId].swim[soldierId]) {
                        killed[r.battle.defenders[color][armyId].swim[soldierId]] = {
                            'soldierId': soldierId
                        };
                    }
                    var unitId = Players.get(color).getArmies().get(armyId).getSwimmingSoldier(soldierId).unitId
                    defense.append(
                        $('<div>')
                            .attr('id', 'unit' + soldierId)
                            .css('background', 'url(' + Unit.getImage(unitId, color) + ') no-repeat')
                            .addClass('battleUnit')
                    );
                }
                for (var soldierId in r.battle.defenders[color][armyId].fly) {
                    if (r.battle.defenders[color][armyId].fly[soldierId]) {
                        killed[r.battle.defenders[color][armyId].fly[soldierId]] = {
                            'soldierId': soldierId
                        };
                    }
                    var unitId = Players.get(color).getArmies().get(armyId).getFlyingSoldier(soldierId).unitId
                    defense.append(
                        $('<div>')
                            .attr('id', 'unit' + soldierId)
                            .css('background', 'url(' + Unit.getImage(unitId, color) + ') no-repeat')
                            .addClass('battleUnit')
                    );
                }
                for (var heroId in r.battle.defenders[color][armyId].hero) {
                    if (r.battle.defenders[color][armyId].hero[heroId]) {
                        killed[r.battle.defenders[color][armyId].hero[heroId]] = {
                            'heroId': heroId
                        };
                    }
                    defense.append(
                        $('<div>')
                            .attr('id', 'hero' + heroId)
                            .css('background', 'url(' + Hero.getImage(color) + ') no-repeat')
                            .addClass('battleUnit')
                    );
                }
            }

            defenseLayout.append($('<div>').html(Players.get(color).getLongName() + ' (' + translations.defence + ')'))

            if (r.battle.castleId) {
                defenseLayout.append(
                    $('<div>')
                        .addClass('castle')
                        .css({
                            position: 'static',
                            background: 'url(/img/game/castles/' + color + '.png) center center no-repeat',
                            margin: '0 auto'
                        })
                )
            }

            if (r.battle.towerId) {
                defenseLayout.append(
                    $('<div>')
                        .addClass('tower')
                        .css({
                            position: 'static',
                            background: 'url(/img/game/towers/' + color + '.png) center center no-repeat',
                            margin: '0 auto'
                        })
                )
            }

            defenseLayout.append(defense)
        }

        var div = $('<div>')
            .append($('<p>').html('&nbsp;'))
            .append(
            $('<div>')
                .addClass('grass')
                .append(defenseLayout)
                .append($('<p>').html('&nbsp;'))
                .append(attackLayout)
        )

        Message.simple(translations.battle, div);

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

        for (k in Me.getBattleSequence(type)) {
            var unitId = Me.getBattleSequence(type)[k],
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
                        src: Unit.getImage(unitId, Me.getColor()),
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
            .append($('<br>'))
            .append(configuration('attack'))

        var id = Message.show(translations.battleConfiguration, div)
        Message.ok(id, WebSocketGame.battleAttack)
        Message.cancel(id)

        $("#sortable").sortable()
        $("#sortable").disableSelection()

    }
    this.defence = function () {
        var div = $('<div>')
            .append($('<div>').html(translations.changeBattleDefenceSequenceByMovingUnits))
            .append($('<br>'))
            .append(configuration('defense'))

        var id = Message.show(translations.battleConfiguration, div)
        Message.ok(id, WebSocketGame.battleDefence)
        Message.cancel(id)

        $("#sortable").sortable()
        $("#sortable").disableSelection()

    }
}