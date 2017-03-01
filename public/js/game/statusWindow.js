var StatusWindow = new function () {
    var statusRowContent = function (id, soldier, attackFlyBonus, attackHeroBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus) {
            if (Units.get(soldier.unitId).fly) {
                var fly = 1
                attackFlyBonus = ''
            } else {
                var fly = 0
            }

            return $('<tr>').addClass('row').css('cursor', 'pointer')
                .append($('<td>').addClass('name').html(Units.get(soldier.unitId).name_lang))
                .append($('<td>')
                    .append($('<div>')
                        .append($('<div>').addClass('attr').html(translations.Attack + ': '))
                        .append($('<div>').addClass('attr value')
                            .append($('<div>').html(Units.get(soldier.unitId).a + fly))
                            .append(attackFlyBonus.clone())
                            .append(attackHeroBonus.clone())
                        )
                    )
                    .append($('<div>')
                        .append($('<div>').addClass('attr').html(translations.Defence + ': '))
                        .append($('<div>').addClass('attr value')
                            .append($('<div>').html(Units.get(soldier.unitId).d))
                            .append(defenseHeroBonus.clone())
                            .append(defenseTowerBonus.clone())
                            .append(defenseCastleBonus.clone())
                        )
                    )
                )
                .append($('<td>')
                    .append($('<div>')
                        .append($('<div>').addClass('attr').html(translations.Moves + ': '))
                        .append($('<div>').addClass('attr value').html(soldier.movesLeft + '/' + Units.get(soldier.unitId).moves))
                    )
                    .append($('<div>')
                        .append($('<div>').addClass('attr').html(translations.Cost + ': '))
                        .append($('<div>').addClass('attr value').html(Units.get(soldier.unitId).cost))
                    )
                    .append($('<input>').hide().attr({
                        type: 'checkbox',
                        name: 'soldierId',
                        value: id
                    }))
                )
                .click(function () {
                    var input = $(this).find('input')
                    if (input.prop('checked')) {
                        input.prop('checked', false)
                        $(this).removeClass('selected')
                    } else {
                        input.prop('checked', true)
                        $(this).addClass('selected')
                    }
                })
        },
        buttons = function (field, castleDefense, army) {
            var html = $('<div>')
                .append(
                    $('<div>').addClass('iconButton buttonColors')
                        .click(function () {
                            Me.disband()
                        })
                        .append($('<div>'))
                        .attr({
                            id: 'disbandArmy',
                            title: translations.Disbandarmy
                        })
                )
                .append(
                    $('<div>')
                        .addClass('iconButton buttonColors')
                        .click(function () {
                            WebSocketSendGame.fortify()
                        })
                        .append($('<div>'))
                        .attr({
                            id: 'quitArmy',
                            title: translations.quitArmy + ' (f)'
                        })
                )
                .append(
                    $('<div>')
                        .addClass('iconButton buttonColors')
                        .click(function () {
                            Me.skip()
                        })
                        .append($('<div>'))
                        .attr({
                            id: 'skipArmy',
                            title: translations.skipArmy + ' (space)'
                        })
                )

            if (field.getRuinId() && army.getHeroKey()) {
                html.append(
                    $('<div>')
                        .addClass('iconButton buttonColors')
                        .click(function () {
                            WebSocketSendGame.ruin()
                        })
                        .append($('<div>'))
                        .attr({
                            id: 'searchRuins',
                            title: translations.searchRuins + ' (r)'
                        })
                )
            }

            if (castleDefense) {
                if (castleDefense < 4) {
                    html.append(
                        $('<div>').addClass('iconButton buttonColors')
                            .click(function () {
                                CastleWindow.build()
                            })
                            .append($('<div>'))
                            .attr({
                                id: 'buildCastleDefense',
                                title: translations.buildCastleDefense + ' (b)'
                            })
                    )
                }

                html.append(
                    $('<div>')
                        .addClass('iconButton buttonColors')
                        .click(function () {
                            CastleWindow.raze()
                        })
                        .append($('<div>'))
                        .attr({
                            id: 'razeCastle',
                            title: translations.razeCastle
                        })
                )
            }

            return $('<div>').addClass('status').append(html)
        }

    this.show = function () {
        var backgroundColor = Players.get(Me.getColor()).getBackgroundColor(),
            army = Me.getArmy(Me.getSelectedArmyId()),
            field = Fields.get(army.getX(), army.getY()),
            bonusTower = 0,
            castleDefense = Me.getMyCastleDefenseFromPosition(army.getX(), army.getY()),
            attackPoints = 0,
            defensePoints = 0,
            attackFlyBonus = $('<div>'),
            defenseFlyBonus = $('<div>'),
            attackHeroBonus = $('<div>'),
            defenseHeroBonus = $('<div>'),
            defenseTowerBonus = $('<div>'),
            defenseCastleBonus = $('<div>'),
            div = buttons(field, castleDefense, army),
            table = $('<table>'),
            number = 0

        if (field.getTowerId()) {
            bonusTower = 1;
        }

        if (army.getFlyBonus()) {
            attackFlyBonus.html(' +1').addClass('value plus')
        }
        if (army.getHeroBonus()) {
            attackHeroBonus.html(' +1').addClass('value plus')
            defenseHeroBonus.html(' +1').addClass('value plus')
        }
        if (bonusTower) {
            defenseTowerBonus.html(' +1').addClass('value plus')
        }
        if (castleDefense) {
            defenseCastleBonus.html(' +' + castleDefense).addClass('value plus')
        }

        for (var soldierId in army.getWalkingSoldiers()) {
            number++
            table.append(statusRowContent(soldierId, army.getWalkingSoldier(soldierId), attackFlyBonus, attackHeroBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
        }
        for (var soldierId in army.getSwimmingSoldiers()) {
            number++
            table.append(statusRowContent(soldierId, army.getSwimmingSoldier(soldierId), attackFlyBonus, attackHeroBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
        }
        for (var soldierId in army.getFlyingSoldiers()) {
            number++
            table.append(statusRowContent(soldierId, army.getFlyingSoldier(soldierId), attackFlyBonus, attackHeroBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
        }
        for (var heroId in army.getHeroes()) {
            number++
            var hero = army.getHero(heroId)
            table.append(
                $('<tr>')
                    .addClass('row').css('cursor', 'pointer')
                    .append($('<td>').addClass('name').html(hero.name))
                    .append($('<td>')
                        .append($('<div>')
                            .append($('<div>').addClass('attr').html(translations.Attack + ': '))
                            .append($('<div>').addClass('attr value')
                                .append($('<div>').html(hero.attack + 1))
                                .append(attackFlyBonus.clone())
                            )
                        )
                        .append($('<div>')
                            .append($('<div>').addClass('attr').html(translations.Defence + ': '))
                            .append($('<div>').addClass('attr value')
                                .append($('<div>').html(hero.defense + 1))
                                .append(defenseFlyBonus.clone())
                                .append(defenseTowerBonus.clone())
                                .append(defenseCastleBonus.clone())
                            )
                        )
                    )
                    .append($('<td>')
                        .append($('<div>')
                            .append($('<div>').addClass('attr').html(translations.Moves + ': '))
                            .append($('<div>').addClass('attr value').html(hero.movesLeft + '/' + hero.moves))
                        )
                        .append($('<div>')
                            .append($('<div>').addClass('attr').html(translations.Cost + ': '))
                            .append($('<div>').addClass('attr value').html('0'))
                        )
                        .append($('<input>').hide().attr({
                            type: 'checkbox',
                            name: 'heroId',
                            value: heroId
                        }))
                    )
                    .click(function () {
                        var input = $(this).find('input')
                        if (input.prop('checked')) {
                            input.prop('checked', false)
                            $(this).removeClass('selected')
                        } else {
                            input.prop('checked', true)
                            $(this).addClass('selected')
                        }
                    })
            )
        }

        var id = Message.show(translations.armyStatus, div.append(table))
        Message.addButton(id, 'cancel')
        if (number > 1) {
            Message.addButton(id, 'splitArmy', WebSocketSendGame.split)
        }
    }
}