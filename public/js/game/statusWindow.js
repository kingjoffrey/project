var StatusWindow = new function () {
    var statusContent = function (numberOfUnits, soldier, color, attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus) {
        return $('<div>')
            .addClass('row')
            .append($('<div>')
                .addClass('rowContent')
                .append($('<div>').addClass('nr').html(numberOfUnits))
                .append($('<div>').addClass('img').html(
                    $('<img>').attr({
                        'src': Unit.getImage(soldier.unitId, color),
                        'id': 'unit' + soldier.soldierId
                    })
                ))
                .append($('<table>')
                    .addClass('leftTable')
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
                .append($('<table>')
                    .addClass('rightTable')
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
        var div = $('<div>').addClass('status'),
            numberOfUnits = 0,
            bonusTower = 0,
            army = Me.getArmy(Me.getSelectedArmyId()),
            color = Me.getColor(),
            castleDefense = Me.getMyCastleDefenseFromPosition(army.getX(), army.getY()),
            attackPoints = 0,
            defensePoints = 0,
            attackFlyBonus = $('<div>'),
            defenseFlyBonus = $('<div>'),
            attackHeroBonus = $('<div>'),
            defenseHeroBonus = $('<div>'),
            defenseTowerBonus = $('<div>'),
            defenseCastleBonus = $('<div>')

        var get = function (i) {
            return function () {
                Zoom.lens.setcenter(castles[i].x, castles[i].y)
            }
        }

        if (Fields.get(army.getX(), army.getY()).getTowerId()) {
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

        for (var i in army.getWalkingSoldiers()) {
            numberOfUnits++
            div.append(statusContent(numberOfUnits, army.getWalkingSoldier(i), color, attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
        }
        for (var i in army.getSwimmingSoldiers()) {
            numberOfUnits++
            div.append(statusContent(numberOfUnits, army.getSwimmingSoldier(i), color, attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
        }
        for (var i in army.getFlyingSoldiers()) {
            numberOfUnits++
            div.append(statusContent(numberOfUnits, army.getFlyingSoldier(i), color, attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
        }
        for (var i in army.getHeroes()) {
            numberOfUnits++
            var hero = army.getHero(i)
            div.append(
                $('<div>')
                    .addClass('row')
                    .append(
                    $('<div>')
                        .addClass('rowContent')
                        .append($('<div>').addClass('nr').html(numberOfUnits))
                        .append($('<div>').addClass('img').html(
                            $('<img>').attr({
                                'src': Hero.getImage(color),
                                'id': 'hero' + hero.heroId
                            })
                        ))
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

        Message.simple(translations.armyStatus, div);
    }
}