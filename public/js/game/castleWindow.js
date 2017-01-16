/**
 * Created by bartek on 31.03.15.
 */
var CastleWindow = new function () {
    var center = function (i) {
            return function () {
                MiniMap.centerOn(CommonMe.getCastle(i).getX(), CommonMe.getCastle(i).getY())
            }
        },
        click = function (i, id) {
            return function () {
                CastleWindow.show(CommonMe.getCastle(i))
                Message.remove(id)
            }
        }


    this.show = function (castle) {
        var time = '',
            checked,
            messageId,
            table = $('<table>'),
            j = 0,
            castleWindow = $('<div>').addClass('showCastle'),
            createProductionTD = function () {
                var array = []

                for (var unitId in castle.getProduction()) {
                    var travelBy = '',
                        unit = Units.get(unitId)

                    if (unitId == castle.getProductionId()) {
                        checked = ' checked'
                        time = castle.getProductionTurn() + '/';
                    } else {
                        checked = ''
                        time = ''
                    }

                    if (unit.canFly) {
                        travelBy = translations.ground + ' / ' + translations.air;
                    } else if (unit.canSwim) {
                        travelBy = translations.water;
                    } else {
                        travelBy = translations.ground;
                    }

                    if (unit.name_lang) {
                        var unitName = unit.name_lang;
                    } else {
                        var unitName = unit.name;
                    }

                    var unitElement = $('<td>')
                        .addClass('unit' + checked)
                        .attr('id', unitId)
                        .append(
                            $('<div>').addClass('name').html('<b>' + unitName + '</b>' + '<br/> (' + travelBy + ')')
                        )
                        .append(
                            $('<div>').addClass('buttons')
                        )
                        .append($('<div>').addClass('attributes')
                            .append($('<div>').html(translations.productionTime + ': ' + time + '<span>' + castle.getProduction()[unitId].time + '</span>'))
                            .append($('<div>').html(translations.costOfLiving + ': ' + '<span>' + unit.cost + '</span>'))
                            .append($('<div>').html(translations.movementPoints + ': ' + '<span>' + unit.moves + '</span>'))
                            .append($('<div>').html(translations.attackPoints + ': ' + '<span>' + unit.a + '</span>'))
                            .append($('<div>').html(translations.defencePoints + ': ' + '<span>' + unit.d + '</span>'))
                        )

                    if (checked) { // jest produkcja
                        if (castle.getRelocationCastleId() && CommonMe.getCastles().has(castle.getRelocationCastleId())) {
                            unitElement.append($('<div>').html(translations.relocatingTo + ' ' + CommonMe.getCastle(castle.getRelocationCastleId()).getName()))
                        }

                        unitElement.find('.buttons')
                            .append(
                                $('<div>') // stop
                                    .html(translations.stop)
                                    .addClass('button buttonColors')
                                    .attr('id', 'stop')
                                    .click(function () {
                                        castle.handle($(this).parent().parent().attr('id'), 1, 0)
                                        Message.remove(messageId)
                                    }))
                    } else { // nie ma produkcji
                        unitElement.find('.buttons')
                            .append(
                                $('<div>') // start
                                    .addClass('button buttonColors')
                                    .attr('id', 'go')
                                    .html(translations.start)
                                    .click(function () {
                                        castle.handle($(this).parent().parent().attr('id'))
                                        Message.remove(messageId)
                                    }))
                        unitElement.find('.buttons')
                            .append(
                                $('<div>') // relokacja
                                    .html(translations.relocation)
                                    .addClass('button buttonColors')
                                    .attr('id', 'relocation')
                                    .click(function () {
                                        castle.handle($(this).parent().parent().attr('id'), 0, 1)
                                        Message.remove(messageId)
                                    }))
                    }

                    array[j] = unitElement
                    j++;
                }
                return array
            }

        if (castle.getCapital()) {
            messageId = Message.show(translations.capitalCity + '&nbsp;' + castle.getName(), castleWindow)
        } else {
            messageId = Message.show(translations.castle + '&nbsp;' + castle.getName(), castleWindow)
        }

        var productionUnitArray = createProductionTD()

        for (var l = 0; l < Math.ceil(j / 2); l++) {
            var m = l * 2,
                tr = $('<tr>').append(productionUnitArray[m])

            if (isSet(productionUnitArray[m + 1])) {
                tr.append(productionUnitArray[m + 1])
            } else {
                tr.append($('<td>').addClass('unit'))
            }
            table.append(tr)
        }

        var next = $('<td>').attr('id', 'next'),
            previous = $('<td>').attr('id', 'previous'),
            nextCastle = CommonMe.findNextCastle(castle.getCastleId()),
            previousCastle = CommonMe.findPreviousCastle(castle.getCastleId())

        if (nextCastle) {
            next.append($('<div>')
                .addClass('button buttonColors next')
                .html('>>')
                .click(function () {
                    CastleWindow.show(nextCastle)
                    MiniMap.centerOn(nextCastle.getX(), nextCastle.getY())
                    Message.remove(messageId)
                }))
        }

        if (previousCastle) {
            previous.append($('<div>')
                .addClass('button buttonColors next')
                .html('<<')
                .click(function () {
                    CastleWindow.show(previousCastle)
                    MiniMap.centerOn(previousCastle.getX(), previousCastle.getY())
                    Message.remove(messageId)
                }))
        }

        castleWindow
            .append(
                $('<table>')
                    .append($('<tr>')
                        .append(previous)
                        .append($('<td>')
                            .append($('<div>')
                                .addClass('button buttonColors cancel')
                                .html(translations.close)
                                .click(function () {
                                    Message.remove(messageId)
                                })))
                        .append(next)))
            .append(
                $('<table>')
                    .append($('<tr>')
                        .append($('<td>').append(translations.incomeFromCastle + ': '))
                        .append($('<td>').append(castle.getIncome() + ' ' + translations.gold_turn))
                    )
            )
            .append($('<div>').addClass('production').append($('<div>').html(translations.availableUnits).addClass('title')).append(table).attr('id', castle.getCastleId()))

        if (castle.getCapital()) {
            castleWindow
                .append($('<div>')
                    .addClass('button buttonColors buttonOff')
                    .attr('id', 'heroResurrection')
                    .html(translations.resurrectHero)
                    .click(function () {
                        Message.remove(messageId)
                    }))
                .append($('<div>')
                    .addClass('button buttonColors buttonOff')
                    .attr('id', 'heroHire')
                    .html(translations.hireHero)
                    .click(function () {
                        Message.remove(messageId)
                    }))
        }

        // relocation from

        var relocatedProduction = CommonMe.getCastles().getRelocatedProduction(castle.getCastleId())
        if (relocatedProduction.length) {
            var relocatingFrom = $('<table>')

            for (var i in relocatedProduction) {
                var castleIdFrom = relocatedProduction[i],
                    castleFrom = CommonMe.getCastle(castleIdFrom),
                    productionId = castleFrom.getProductionId()

                relocatingFrom.append(
                    $('<tr>')
                        .append($('<td>').html(Units.get(productionId).name_lang))
                        .append($('<td>')
                            .html(castleFrom.getProductionTurn() + ' / ' + castleFrom.getProduction()[productionId].time))
                        .append($('<td>')
                            .html(castleFrom.getName())
                            .addClass('button buttonColors')
                            .click(click(castleIdFrom, messageId)))
                        .append($('<td>')
                            .html($('<img>').attr('src', '/img/game/center.png'))
                            .addClass('iconButton buttonColors')
                            .click(center(castleIdFrom)))
                )
            }
            castleWindow
                .append($('<div>').addClass('relocatedProduction').append($('<div>').html(translations.relocatingFrom).addClass('title')).append(relocatingFrom))
        }

        Message.setOverflowHeight(messageId)

        if (castle.getCapital()) {
            if (!CommonMe.findHero() && CommonMe.getGold() >= 100) {
                $('#heroResurrection').removeClass('buttonOff')
                $('#heroHire').addClass('buttonOff')
            } else if (CommonMe.getGold() >= 1000) {
                $('#heroResurrection').addClass('buttonOff')
                $('#heroHire').removeClass('buttonOff')
            } else {
                $('#heroResurrection').addClass('buttonOff')
                $('#heroHire').addClass('buttonOff')
            }
            $('#heroResurrection:not(.buttonOff)').click(function () {
                var id = Message.show(translations.resurrectHero, $('<div>').append(translations.doYouWantToResurrectHeroFor100Gold))
                Message.ok(id, WebSocketSendCommon.resurrection)
                Message.cancel(id)
            })

            $('#heroHire:not(.buttonOff)').click(function () {
                var id = Message.show(translations.hireHero, $('<div>').html(translations.doYouWantToHireNewHeroFor1000Gold))
                Message.ok(id, WebSocketSendCommon.hire)
                Message.cancel(id)
            })
        }
    }
    this.raze = function () {
        if (!CommonMe.getSelectedArmyId()) {
            return;
        }
        var id = Message.show(translations.destroyCastle, $('<div>').html(translations.areYouSure))
        Message.ok(id, WebSocketSendCommon.raze);
        Message.cancel(id)
    }
    this.build = function () {
        if (!CommonMe.getSelectedArmyId()) {
            return;
        }

        var army = CommonMe.getArmy(CommonMe.getSelectedArmyId())
        var castle = CommonMe.getCastle(Fields.get(army.getX(), army.getY()).getCastleId())

        if (castle.getDefense() == 4) {
            var div = $('<div>')
                .append($('<h3>').html(translations.maximumCastleDefenceReached))
                .append($('<div>').html(translations.currentDefense + ': ' + castle.getDefense()))
            var id = Message.show(translations.buildCastleDefense, div);
        } else {
            var costBuildDefense = 0;
            for (i = 1; i <= castle.getDefense(); i++) {
                costBuildDefense += i * 100;
            }
            var newDefense = castle.getDefense() + 1;

            var div = $('<div>')
                .append($('<h3>').html(translations.doYouWantToBuildCastleDefense))
                .append($('<div>').html(translations.currentDefense + ': ' + castle.getDefense()))
                .append($('<div>').html(translations.newDefense + ': ' + newDefense))
                .append($('<div>').html(translations.cost + ': ' + costBuildDefense + ' ' + translations.gold))
            var id = Message.show(translations.buildCastleDefense, div);
            Message.ok(id, WebSocketSendCommon.defense);
        }
        Message.cancel(id)
    }
}
