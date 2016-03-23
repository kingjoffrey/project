var TreasuryWindow = new function () {
    this.treasury = function () {
        var myTowers = CommonMe.getTowers().count(),
            myCastles = 0,
            myCastlesIncome = 0,
            myUnits = 0,
            myUnitsOutcome = 0

        for (var i in CommonMe.getCastles().toArray()) {
            var castle = CommonMe.getCastle(i)
            myCastles++
            myCastlesIncome += castle.getIncome()
        }

        for (var i in CommonMe.getArmies().toArray()) {
            var army = CommonMe.getArmy(i)
            for (var j in army.getWalkingSoldiers()) {
                myUnitsOutcome += Units.get(army.getWalkingSoldier(j).unitId).cost
                myUnits++
            }
            for (var j in army.getFlyingSoldiers()) {
                myUnitsOutcome += Units.get(army.getFlyingSoldier(j).unitId).cost
                myUnits++
            }
            for (var j in army.getSwimmingSoldiers()) {
                myUnitsOutcome += Units.get(army.getSwimmingSoldier(j).unitId).cost
                myUnits++
            }
        }

        var div = $('<div>')
            .addClass('overflow')
            .append($('<h3>').html(translations.income + ':'))
            .append($('<table>')
                .addClass('treasury')
                .append($('<tr>')
                    .append($('<td>').html(myTowers).addClass('r'))
                    .append($('<td>').html(translations.towers).addClass('c'))
                    .append($('<td>').html(myTowers * 5 + ' ' + translations.gold).addClass('r')))
                .append($('<tr>')
                    .append($('<td>').html(myCastles).addClass('r'))
                    .append($('<td>').html(translations.castles).addClass('c'))
                    .append($('<td>').html(myCastlesIncome + ' ' + translations.gold).addClass('r')))
                .append($('<tr>')
                    .append($('<td>'))
                    .append($('<td>'))
                    .append($('<td>').html(myTowers * 5 + myCastlesIncome + ' ' + translations.gold).addClass('r'))))
            .append($('<h3>').html('<br>' + translations.upkeep + ':'))
            .append($('<table>')
                .addClass('treasury')
                .append($('<tr>')
                    .append($('<td>').html(myUnits).addClass('r'))
                    .append($('<td>').html(translations.units).addClass('c'))
                    .append($('<td>').html(myUnitsOutcome + ' ' + translations.gold).addClass('r'))))
            .append($('<h3>').html('<br>' + translations.profit + ':'))
            .append($('<table>')
                .addClass('treasury')
                .append($('<tr>')
                    .append($('<td>').html(myTowers * 5 + myCastlesIncome - myUnitsOutcome + ' ' + translations.goldPerTurn))))

        Message.simple(translations.treasury, div)
    }
    this.income = function () {
        var myTowers = CommonMe.getTowers().count(),
            myCastles = 0,
            myCastlesIncome = 0

        var table = $('<table>')
            .addClass('treasury')

        var click = function (i) {
            return function () {
                MiniMap.centerOn(CommonMe.getCastle(i).getX(), CommonMe.getCastle(i).getY())
            }
        }

        for (var i in CommonMe.getCastles().toArray()) {
            var castle = CommonMe.getCastle(i)
            myCastles++
            myCastlesIncome += castle.getIncome()
            table.append(
                $('<tr>')
                    .append($('<td>'))
                    .append($('<td>').html(castle.getName()))
                    .append($('<td>').html(castle.getIncome() + ' ' + translations.gold).addClass('r'))
                    .click(click(i))
                    .mouseover(function () {
                        $(this).children().css({
                            background: 'lime',
                            color: '#000'
                        })
                    })
                    .mouseout(function () {
                        $(this).children().css({
                            background: '#000',
                            color: 'lime'
                        })
                    })
                    .css('color', 'lime')
            )
        }
        table.append(
            $('<tr>')
                .append($('<td>').html(myCastles).addClass('r'))
                .append($('<td>').html(translations.castles).addClass('c'))
                .append($('<td>').html(myCastlesIncome + ' ' + translations.gold).addClass('r'))
        ).append(
            $('<tr>')
                .append($('<td>').html(myTowers).addClass('r'))
                .append($('<td>').html(translations.towers).addClass('c'))
                .append($('<td>').html(myTowers * 5 + ' ' + translations.gold).addClass('r'))
        ).append(
            $('<tr>')
                .append($('<td colspan="3">').html(myTowers * 5 + myCastlesIncome + ' ' + translations.gold).addClass('r'))
        )


        var id = Message.simple(translations.income, table)
        Message.setOverflowHeight(id)
    }
    this.upkeep = function () {
        var myUnits = 0,
            myUnitsGold = 0

        var table = $('<table>')
            .addClass('treasury')

        var center = function (i) {
            return function () {
                MiniMap.centerOn(CommonMe.getArmy(i).getX(), CommonMe.getArmy(i).getY())
            }
        }

        for (var i in CommonMe.getArmies().toArray()) {
            var army = CommonMe.getArmy(i)
            for (var j in army.getWalkingSoldiers()) {
                var soldier = army.getWalkingSoldier(j)
                myUnitsGold += Units.get(soldier.unitId).cost
                myUnits++
                table.append(
                    $('<tr>')
                        .append($('<td>').html($('<img>').attr('src', Unit.getImage(soldier.unitId, CommonMe.getColor()))))
                        .append($('<td>').html(Units.get(soldier.unitId).name_lang))
                        .append($('<td>').html(Units.get(soldier.unitId).cost + ' ' + translations.gold).addClass('r'))
                        .append(
                            $('<td>')
                                .html($('<img>').attr('src', '/img/game/center.png'))
                                .addClass('iconButton buttonColors')
                                .click(center(i))
                        )
                )
            }
            for (var j in army.getFlyingSoldiers()) {
                var soldier = army.getFlyingSoldier(j)
                myUnitsGold += Units.get(soldier.unitId).cost
                myUnits++
                table.append(
                    $('<tr>')
                        .append($('<td>').html($('<img>').attr('src', Unit.getImage(soldier.unitId, CommonMe.getColor()))))
                        .append($('<td>').html(Units.get(soldier.unitId).name_lang))
                        .append($('<td>').html(Units.get(soldier.unitId).cost + ' ' + translations.gold).addClass('r'))
                        .append(
                            $('<td>')
                                .html($('<img>').attr('src', '/img/game/center.png'))
                                .addClass('iconButton buttonColors')
                                .click(center(i))
                        )
                )
            }
            for (var j in army.getSwimmingSoldiers()) {
                var soldier = army.getSwimmingSoldier(j)
                myUnitsGold += Units.get(soldier.unitId).cost
                myUnits++
                table.append(
                    $('<tr>')
                        .append($('<td>').html($('<img>').attr('src', Unit.getImage(soldier.unitId, CommonMe.getColor()))))
                        .append($('<td>').html(Units.get(soldier.unitId).name_lang))
                        .append($('<td>').html(Units.get(soldier.unitId).cost + ' ' + translations.gold).addClass('r'))
                        .append(
                            $('<td>')
                                .html($('<img>').attr('src', '/img/game/center.png'))
                                .addClass('iconButton buttonColors')
                                .click(center(i))
                        )
                )
            }
        }

        table.append(
            $('<tr>')
                .append($('<td colspan="3">').html(myUnitsGold + ' ' + translations.gold).addClass('r'))
        )

        var id = Message.simple(translations.upkeep, table)
        Message.setOverflowHeight(id)
    }
}