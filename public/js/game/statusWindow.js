var StatusWindow = new function () {
    var backgroundColor,
        statusRowContent = function (id, soldier, attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus) {
            return $('<div>').addClass('row')
                .append($('<div>').addClass('rowContent')
                    .append($('<div>').addClass('canvas').attr({'id': 'soldier' + id}))
                    .append($('<table>').addClass('leftTable')
                        .append($('<tr>')
                            .append($('<td>').html(translations.currentMoves + ': '))
                            .append($('<td>').html(soldier.movesLeft).addClass('value'))
                        )
                        .append($('<tr>')
                            .append($('<td>').html(translations.defaultMoves + ': '))
                            .append($('<td>').html(Units.get(soldier.unitId).moves).addClass('value'))
                        )
                        .append($('<tr>')
                            .append($('<td>').html(translations.attackPoints + ': '))
                            .append($('<td>')
                                .append($('<div>').html(Units.get(soldier.unitId).a))
                                .append(attackFlyBonus.clone())
                                .append(attackHeroBonus.clone())
                                .addClass('value')
                            )
                        )
                        .append($('<tr>')
                            .append($('<td>').html(translations.defencePoints + ': '))
                            .append($('<td>')
                                .append($('<div>').html(Units.get(soldier.unitId).d))
                                .append(defenseFlyBonus.clone())
                                .append(defenseHeroBonus.clone())
                                .append(defenseTowerBonus.clone())
                                .append(defenseCastleBonus.clone())
                                .addClass('value')
                            )
                        )
                    )
                    .append($('<table>').addClass('rightTable')
                        .append($('<tr>')
                            .append($('<td>').html(translations.movementCostThroughTheForest + ': '))
                            .append($('<td>').html(Units.get(soldier.unitId).f).addClass('value'))
                        )
                        .append($('<tr>')
                            .append($('<td>').html(translations.movementCostThroughTheSwamp + ': '))
                            .append($('<p>').html(Units.get(soldier.unitId).s).addClass('value')))
                        .append($('<tr>')
                            .append($('<td>').html(translations.movementCostThroughTheHills + ': '))
                            .append($('<p>').html(Units.get(soldier.unitId).h).addClass('value'))
                        )
                    )
                )
        }

    this.show = function () {
        var bonusTower = 0,
            army = CommonMe.getArmy(CommonMe.getSelectedArmyId()),
            castleDefense = CommonMe.getMyCastleDefenseFromPosition(army.getX(), army.getY()),
            attackPoints = 0,
            defensePoints = 0,
            attackFlyBonus = $('<div>'),
            defenseFlyBonus = $('<div>'),
            attackHeroBonus = $('<div>'),
            defenseHeroBonus = $('<div>'),
            defenseTowerBonus = $('<div>'),
            defenseCastleBonus = $('<div>'),
            field = Fields.get(army.getX(), army.getY()),
            showCastle = 'buttonOff',
            buildCastleDefense = 'buttonOff',
            searchRuins = 'buttonOff',
            splitArmy = 'buttonOff'

        backgroundColor = Players.get(CommonMe.getColor()).getBackgroundColor()

        if (field.getRuinId()) {
            searchRuins = ''
        }

        if (field.getTowerId()) {
            bonusTower = 1;
        }

        if (army.getNumberOfUnits() > 1) {
            splitArmy = ''
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
            showCastle = ''
            if (castleDefense < 4) {
                buildCastleDefense = ''
            }
        }

        var div = $('<div>').addClass('status').append(
            $('<div>')
                .append(
                    $('<a>').attr({
                        id: 'disbandArmy',
                        title: 'Disband army'
                    }).addClass('iconButton buttonColors').click(function () {
                        CommonMe.disband()
                    })
                )
                .append(
                    $('<a>').attr({
                        id: 'showCastle',
                        title: 'Show castle (c)'
                    }).addClass('iconButton buttonColors ' + showCastle).click(function () {
                        var castle = CommonMe.getCastle(field.getCastleId())
                        if (isSet(castle)) {
                            CastleWindow.show(castle)
                        }
                    })
                )
                .append(
                    $('<a>').attr({
                        id: 'buildCastleDefense',
                        title: 'Build defense (b)'
                    }).addClass('iconButton buttonColors ' + buildCastleDefense).click(function () {
                        CastleWindow.build()
                    })
                )
                .append(
                    $('<a>').attr({
                        id: 'razeCastle',
                        title: 'Raze castle'
                    }).addClass('iconButton buttonColors ' + showCastle).click(function () {
                        CastleWindow.raze()
                    })
                )
                .append(
                    $('<a>').attr({
                        id: 'searchRuins',
                        title: 'Search ruins (r)'
                    }).addClass('iconButton buttonColors ' + searchRuins).click(function () {
                        WebSocketSend.ruin()
                    })
                )
                .append(
                    $('<a>').attr({
                        id: 'splitArmy',
                        title: 'Split army'
                    }).addClass('iconButton buttonColors ' + splitArmy).click(function () {
                        if (!CommonMe.getSelectedArmyId()) {
                            return
                        }

                        SplitWindow.show()
                    })
                )
                .append(
                    $('<a>').attr({
                        id: 'quitArmy',
                        title: 'Fortify army (f)'
                    }).addClass('iconButton buttonColors').click(function () {
                        WebSocketSend.fortify()
                    })
                )
                .append(
                    $('<a>').attr({
                        id: 'skipArmy',
                        title: 'Skip army (space)'
                    }).addClass('iconButton buttonColors').click(function () {
                        CommonMe.skip()
                    })
                )
        )

        for (var soldierId in army.getWalkingSoldiers()) {
            div.append(statusRowContent(soldierId, army.getWalkingSoldier(soldierId), attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
        }
        for (var soldierId in army.getSwimmingSoldiers()) {
            div.append(statusRowContent(soldierId, army.getSwimmingSoldier(soldierId), attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
        }
        for (var soldierId in army.getFlyingSoldiers()) {
            div.append(statusRowContent(soldierId, army.getFlyingSoldier(soldierId), attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
        }
        for (var heroId in army.getHeroes()) {
            var hero = army.getHero(heroId)
            div.append(
                $('<div>')
                    .addClass('row')
                    .append(
                        $('<div>')
                            .addClass('rowContent')
                            .append($('<div>').addClass('canvas').attr({'id': 'hero' + heroId}))
                            .append(
                                $('<table>').addClass('leftTable')
                                    .append(
                                        $('<tr>')
                                            .append($('<td>').html(translations.currentMoves + ': '))
                                            .append($('<td>').html(hero.movesLeft).addClass('value'))
                                    )
                                    .append(
                                        $('<tr>')
                                            .append($('<td>').html(translations.defaultMoves + ': '))
                                            .append($('<td>').html(hero.moves).addClass('value'))
                                    )
                                    .append(
                                        $('<tr>')
                                            .append($('<td>').html(translations.attackPoints + ': '))
                                            .append(
                                                $('<td>')
                                                    .append($('<div>').html(hero.attack))
                                                    .append(attackFlyBonus.clone())
                                                    //                                                    .append(attackHeroBonus.clone())
                                                    .addClass('value')
                                            )
                                    )
                                    .append(
                                        $('<tr>')
                                            .append($('<td>').html(translations.defencePoints + ': '))
                                            .append(
                                                $('<td>')
                                                    .append($('<div>').html(hero.defense))
                                                    .append(defenseFlyBonus.clone())
                                                    //                                                    .append(defenseHeroBonus.clone())
                                                    .append(defenseTowerBonus.clone())
                                                    .append(defenseCastleBonus.clone())
                                                    .addClass('value')
                                            )
                                    )
                            )
                            .append(
                                $('<table>').addClass('rightTable')
                            )
                    )
            );
        }

        var id = Message.simple(translations.armyStatus, div)
        Message.setOverflowHeight(id)

        for (var soldierId in army.getWalkingSoldiers()) {
            var unitScene = new UnitScene()
            unitScene.init(60, 95)

            UnitModels.addUnit(backgroundColor, Unit.getName(army.getWalkingSoldier(soldierId).unitId), unitScene)
            Renderers.add('soldier' + soldierId, unitScene)
        }
        for (var soldierId in army.getSwimmingSoldiers()) {
            var unitScene = new UnitScene()
            unitScene.init(60, 95)

            UnitModels.addUnit(backgroundColor, Unit.getName(army.getSwimmingSoldier(soldierId).unitId), unitScene)
            Renderers.add('soldier' + soldierId, unitScene)
        }
        for (var soldierId in army.getFlyingSoldiers()) {
            var unitScene = new UnitScene()
            unitScene.init(60, 95)

            UnitModels.addUnit(backgroundColor, Unit.getName(army.getFlyingSoldier(soldierId).unitId), unitScene)
            Renderers.add('soldier' + soldierId, unitScene)
        }
        for (var heroId in army.getHeroes()) {
            var unitScene = new UnitScene()
            unitScene.init(60, 95)

            UnitModels.addHero(backgroundColor, unitScene)
            Renderers.add('hero' + heroId, unitScene)
        }
    }
}