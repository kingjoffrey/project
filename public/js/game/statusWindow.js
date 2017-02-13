var StatusWindow = new function () {
    var statusRowContent = function (id, soldier, attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus) {
            return $('<tr>').addClass('row')
                .append($('<td>').addClass('name').html(Units.get(soldier.unitId).name_lang))
                .append($('<td>')
                    .append($('<div>')
                        .append($('<div>').addClass('attr').html(translations.Moves + ': '))
                        .append($('<div>').addClass('attr value').html(soldier.movesLeft + '/' + Units.get(soldier.unitId).moves))
                    )
                    .append($('<div>')
                        .append($('<div>').addClass('attr').html(translations.attackPoints + ': '))
                        .append($('<div>').addClass('attr value')
                            .append($('<div>').html(Units.get(soldier.unitId).a))
                            .append(attackFlyBonus.clone())
                            .append(attackHeroBonus.clone())
                        )
                    )
                    .append($('<div>')
                        .append($('<div>').addClass('attr').html(translations.defencePoints + ': '))
                        .append($('<div>').addClass('attr value')
                            .append($('<div>').html(Units.get(soldier.unitId).d))
                            .append(defenseFlyBonus.clone())
                            .append(defenseHeroBonus.clone())
                            .append(defenseTowerBonus.clone())
                            .append(defenseCastleBonus.clone())
                        )
                    )
                )
                .append($('<td>')
                    .append($('<div>')
                        .append($('<div>').addClass('attr').html(translations.Forest + ': '))
                        .append($('<div>').addClass('attr value').html(Units.get(soldier.unitId).f))
                    )
                    .append($('<div>')
                        .append($('<div>').addClass('attr').html(translations.Swamp + ': '))
                        .append($('<div>').addClass('attr value').html(Units.get(soldier.unitId).s))
                    )
                    .append($('<div>')
                        .append($('<div>').addClass('attr').html(translations.Hills + ': '))
                        .append($('<div>').addClass('attr value').html(Units.get(soldier.unitId).h))
                    )
                )
        },
        buttons = function (field, castleDefense, army) {
            var showCastle = 'buttonOff',
                buildCastleDefense = 'buttonOff',
                searchRuins = 'buttonOff',
                splitArmy = 'buttonOff'

            if (field.getRuinId()) {
                searchRuins = ''
            }
            if (castleDefense) {
                showCastle = ''
                if (castleDefense < 4) {
                    buildCastleDefense = ''
                }
                if (castleDefense < 4) {
                    buildCastleDefense = ''
                }
            }

            if (army.getNumberOfUnits() > 1) {
                splitArmy = ''
            }

            return $('<div>').addClass('status').append(
                $('<div>')
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
                        $('<div>').addClass('iconButton buttonColors ' + showCastle)
                            .click(function () {
                                var castle = Me.getCastle(field.getCastleId())
                                if (isSet(castle)) {
                                    CastleWindow.show(castle)
                                }
                            })
                            .append($('<div>'))
                            .attr({
                                id: 'showCastle',
                                title: translations.showCastle + ' (c)'
                            })
                    )
                    .append(
                        $('<div>').addClass('iconButton buttonColors ' + buildCastleDefense)
                            .click(function () {
                                CastleWindow.build()
                            })
                            .append($('<div>'))
                            .attr({
                                id: 'buildCastleDefense',
                                title: translations.buildCastleDefense + ' (b)'
                            })
                    )
                    .append(
                        $('<div>')
                            .addClass('iconButton buttonColors ' + showCastle)
                            .click(function () {
                                CastleWindow.raze()
                            })
                            .append($('<div>'))
                            .attr({
                                id: 'razeCastle',
                                title: translations.razeCastle
                            })
                    )
                    .append(
                        $('<div>')
                            .addClass('iconButton buttonColors ' + searchRuins)
                            .click(function () {
                                WebSocketSendGame.ruin()
                            })
                            .append($('<div>'))
                            .attr({
                                id: 'searchRuins',
                                title: translations.searchRuins + ' (r)'
                            })
                    )
                    .append(
                        $('<div>')
                            .addClass('iconButton buttonColors ' + splitArmy)
                            .click(function () {
                                if (!Me.getSelectedArmyId()) {
                                    return
                                }
                                if (splitArmy) {
                                    return
                                }
                                SplitWindow.show()
                            })
                            .append($('<div>'))
                            .attr({
                                id: 'splitArmy',
                                title: translations.splitArmy
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
            )
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
            table = $('<table>')

        if (field.getTowerId()) {
            bonusTower = 1;
        }

        if (army.getFlyBonus()) {
            attackFlyBonus.html(' +1').addClass('value plus')
            defenseFlyBonus.html(' +1').addClass('value plus')
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
            table.append(statusRowContent(soldierId, army.getWalkingSoldier(soldierId), attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
        }
        for (var soldierId in army.getSwimmingSoldiers()) {
            table.append(statusRowContent(soldierId, army.getSwimmingSoldier(soldierId), attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
        }
        for (var soldierId in army.getFlyingSoldiers()) {
            table.append(statusRowContent(soldierId, army.getFlyingSoldier(soldierId), attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
        }
        for (var heroId in army.getHeroes()) {
            var hero = army.getHero(heroId)
            table.append(
                $('<tr>')
                    .addClass('row')
                    .append($('<td>').addClass('name').html(translations.hero))
                    .append($('<td>')
                        .append($('<div>')
                            .append($('<div>').addClass('attr').html(translations.Moves + ': '))
                            .append($('<div>').addClass('attr value').html(hero.movesLeft + '/' + hero.moves))
                        )
                        .append($('<div>')
                            .append($('<div>').addClass('attr').html(translations.attackPoints + ': '))
                            .append($('<div>').addClass('attr value')
                                    .append($('<div>').html(hero.attack))
                                    .append(attackFlyBonus.clone())
                                //                                                    .append(attackHeroBonus.clone())
                            )
                        )
                        .append($('<div>')
                            .append($('<div>').addClass('attr').html(translations.defencePoints + ': '))
                            .append($('<div>').addClass('attr value')
                                .append($('<div>').html(hero.defense))
                                .append(defenseFlyBonus.clone())
                                //                                                    .append(defenseHeroBonus.clone())
                                .append(defenseTowerBonus.clone())
                                .append(defenseCastleBonus.clone())
                            )
                        )
                    )
                    .append($('<td>')
                        .append($('<div>')
                            .append($('<div>').addClass('attr').html(translations.Forest + ': '))
                            .append($('<div>').addClass('attr value').html(3))
                        )
                        .append($('<div>')
                            .append($('<div>').addClass('attr').html(translations.Swamp + ': '))
                            .append($('<div>').addClass('attr value').html(4))
                        )
                        .append($('<div>')
                            .append($('<div>').addClass('attr').html(translations.Hills + ': '))
                            .append($('<div>').addClass('attr value').html(5))
                        )
                    )
            )
        }

        var id = Message.simple(translations.armyStatus, div.append(table))

        // for (var soldierId in army.getWalkingSoldiers()) {
        //     $('soldier' + soldierId).html(Unit.getName(army.getWalkingSoldier(soldierId).unitId))
        // }
    }
}