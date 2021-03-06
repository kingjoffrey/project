var TreasuryWindow = new function () {
    this.treasury = function () {
        var myTowers = Me.getTowers().count(),
            myCastles = 0,
            myCastlesIncome = 0,
            myUnits = 0,
            myUnitsOutcome = 0

        for (var i in Me.getCastles().toArray()) {
            var castle = Me.getCastle(i)
            myCastles++
            myCastlesIncome += castle.getIncome()
        }

        for (var i in Me.getArmies().toArray()) {
            var army = Me.getArmy(i)
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
            .addClass('overflow mygold')
            .append($('<h3>').html('<br>' + translations.income))
            .append($('<table>')
                .addClass('treasury')
                .append($('<tr>')
                    .append($('<td>').html(translations.towers).addClass('c'))
                    .append($('<td>').html(myTowers).addClass('r'))
                    .append($('<td>').html(myTowers * 5 + ' ' + translations.gold).addClass('r')))
                .append($('<tr>')
                    .append($('<td>').html(translations.castles).addClass('c'))
                    .append($('<td>').html(myCastles).addClass('r'))
                    .append($('<td>').html(myCastlesIncome + ' ' + translations.gold).addClass('r')))
                .append($('<tr>')
                    .append($('<td>'))
                    .append($('<td>'))
                    .append($('<td>').html(myTowers * 5 + myCastlesIncome + ' ' + translations.gold).addClass('r'))))
            .append($('<h3>').html('<br>' + translations.upkeep))
            .append($('<table>')
                .addClass('treasury')
                .append($('<tr>')
                    .append($('<td>').html(translations.soldiers).addClass('c'))
                    .append($('<td>').html(myUnits).addClass('r'))
                    .append($('<td>').html(myUnitsOutcome + ' ' + translations.gold).addClass('r'))))
            .append($('<h3>').html('<br>' + translations.profit))
            .append($('<table>')
                .addClass('treasury')
                .append($('<tr>')
                    .append($('<td>').html(myTowers * 5 + myCastlesIncome - myUnitsOutcome + ' ' + translations.goldPerTurn))))

        Message.simple(translations.treasury, div)
    }
    this.income = function () {
        var myTowers = Me.getTowers().count(),
            myCastles = 0,
            myCastlesIncome = 0

        var table = $('<table>')
            .addClass('treasury')

        var click = function (i) {
            return function () {
                GameScene.centerOn(Me.getCastle(i).getX(), Me.getCastle(i).getY())
            }
        }

        for (var i in Me.getCastles().toArray()) {
            var castle = Me.getCastle(i)
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
    }
    this.upkeep = function () {
        var myUnits = 0,
            myUnitsGold = 0

        var table = $('<table>')
            .addClass('treasury')

        var center = function (i) {
            return function () {
                GameScene.centerOn(Me.getArmy(i).getX(), Me.getArmy(i).getY())
            }
        }

        for (var i in Me.getArmies().toArray()) {
            var army = Me.getArmy(i)
            for (var j in army.getWalkingSoldiers()) {
                var soldier = army.getWalkingSoldier(j)
                myUnitsGold += Units.get(soldier.unitId).cost
                myUnits++
                table.append(
                    $('<tr>')
                        .append($('<td>').html($('<img>').attr('src', Unit.getImage(soldier.unitId, Me.getColor()))))
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
                        .append($('<td>').html($('<img>').attr('src', Unit.getImage(soldier.unitId, Me.getColor()))))
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
                        .append($('<td>').html($('<img>').attr('src', Unit.getImage(soldier.unitId, Me.getColor()))))
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
    }
}