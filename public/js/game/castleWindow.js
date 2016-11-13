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
            table = $('<table>'),
            info = $('<table>'),
            j = 0,
            td = new Array(),
            window = $('<div>').addClass('showCastle')

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
                var name = unit.name_lang;
            } else {
                var name = unit.name;
            }

            td[j] = $('<td>')
                .addClass('unit' + checked)
                .attr('id', unitId)
                .append(
                    $('<div>')
                        .append($('<div>').addClass('canvas')
                            .attr({'id': 'unit' + unitId}))
                        .append($('<div>').addClass('top')
                            .append($('<div>').addClass('name')
                                .html('<b>' + name + '</b>' + '<br/> (' + travelBy + ')')
                            ))
                )
                .append($('<div>').addClass('attributes')
                    .append($('<p>').html(translations.productionTime + ': ' + time + '<span>' + castle.getProduction()[unitId].time + '</span>&nbsp;' + translations.turn))
                    .append($('<p>').html(translations.costOfLiving + ': ' + '<span>' + unit.cost + '</span>&nbsp;' + translations.gold + '/' + translations.turn))
                    .append($('<p>').html(translations.movementPoints + ': ' + '<span>' + unit.moves + '</span>'))
                    .append($('<p>').html(translations.attackPoints + ': ' + '<span>' + unit.a + '</span>'))
                    .append($('<p>').html(translations.defencePoints + ': ' + '<span>' + unit.d + '</span>')))
            j++;
        }
        var k = Math.ceil(j / 2);
        for (l = 0; l < k; l++) {
            var tr = $('<tr>');
            var m = l * 2;
            tr.append(td[m]);
            if (typeof td[m + 1] != 'undefined') {
                tr.append(td[m + 1]);
            }
            table.append(tr);
        }

        // stop & relocation

        var stopButtonOff = ' buttonOff'
        var relocationButtonOff = ' buttonOff'
        if (castle.getProductionId()) {
            if (CommonMe.countCastles() > 1) {
                var relocationButtonOff = ''
            }
            var stopButtonOff = ''
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
                    Message.remove(id)
                }))
        }
        if (previousCastle) {
            previous.append($('<div>')
                .addClass('button buttonColors next')
                .html('<<')
                .click(function () {
                    CastleWindow.show(previousCastle)
                    MiniMap.centerOn(previousCastle.getX(), previousCastle.getY())
                    Message.remove(id)
                }))
        }

        info
            .append($('<tr>')
                .append(previous)
                .append($('<td>').append(translations.castleDefense + ': '))
                .append($('<td>').append(castle.getDefense()))
                .append(next))
            .append($('<tr>')
                .append($('<td>'))
                .append($('<td>').append(translations.incomeFromCastle + ': '))
                .append($('<td>').append(castle.getIncome() + ' ' + translations.gold_turn))
                .append($('<td>')
                    .append($('<div>')
                        .html($('<img>').attr({src: '/img/game/center.png'}))
                        .addClass('iconButton buttonColors')
                        .click(center(castle.getCastleId()))
                        .attr('id', 'center'))))
        window
            .append(info)
            .append($('<div>').addClass('production').append($('<div>').html(translations.availableUnits).addClass('title')).append(table).attr('id', castle.getCastleId()))

        if (castle.getCapital()) {
            var id = Message.show(translations.capitalCity + '&nbsp;' + castle.getName(), window)
        } else {
            var id = Message.show(translations.castle + '&nbsp;' + castle.getName(), window)
        }
        window
            .append($('<div>')
                .html(translations.stopProduction)
                .addClass('button buttonColors' + stopButtonOff)
                .attr('id', 'stop')
                .click(function () {
                    if ($('.unit.checked').attr('id')) {
                        castle.handle($('.unit.checked').attr('id'), 1, 0)
                        Message.remove(id)
                    }
                }))
            .append($('<div>')
                .html(translations.productionRelocation)
                .addClass('button buttonColors' + relocationButtonOff)
                .attr('id', 'relocation')
                .click(function () {
                    if ($('.unit.checked').attr('id')) {
                        castle.handle($('.unit.checked').attr('id'), 0, 1)
                        Message.remove(id)
                    } else if ($('.unit.select').attr('id')) {
                        castle.handle($('.unit.select').attr('id'), 0, 1)
                        Message.remove(id)
                    }
                }))
            .append($('<div>')
                .addClass('button buttonColors buttonOff')
                .attr('id', 'go')
                .html(translations.startProduction)
                .click(function () {
                    if ($('.unit.select').attr('id')) {
                        castle.handle($('.unit.select').attr('id'))
                        Message.remove(id)
                    }
                }))
            .append($('<div>')
                .addClass('button buttonColors cancel')
                .html(translations.close)
                .click(function () {
                    Message.remove(id)
                }))
        if (castle.getCapital()) {
            window
                .append($('<div>')
                    .addClass('button buttonColors buttonOff')
                    .attr('id', 'heroResurrection')
                    .html(translations.resurrectHero)
                    .click(function () {
                        Message.remove(id)
                    }))
                .append($('<div>')
                    .addClass('button buttonColors buttonOff')
                    .attr('id', 'heroHire')
                    .html(translations.hireHero)
                    .click(function () {
                        Message.remove(id)
                    }))
        }

        // relocation to

        if (castle.getProductionId() && castle.getRelocationCastleId() && CommonMe.getCastles().has(castle.getRelocationCastleId())) {
            window
                .append($('<div>').addClass('relocatedProduction').append($('<div>').html(translations.relocatingTo).addClass('title')).append(
                    $('<table>').append(
                        $('<tr>').append(
                            $('<td>').append($('<img>').attr('src', Unit.getImage(castle.getProductionId(), CommonMe.getColor()))))
                            .append($('<td>')
                                .html(castle.getProductionTurn() + ' / ' + castle.getProduction()[castle.getProductionId()].time))
                            .append($('<td>')
                                .html(CommonMe.getCastle(castle.getRelocationCastleId()).getName())
                                .addClass('button buttonColors')
                                .click(function () {
                                    CastleWindow.show(CommonMe.getCastle(castle.getRelocationCastleId()))
                                    Message.remove(id)
                                }))
                            .append($('<td>')
                                .html($('<img>').attr('src', '/img/game/center.png'))
                                .addClass('iconButton buttonColors')
                                .click(center(castle.getRelocationCastleId()))))))
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
                    $('<tr>').append(
                        $('<td>').append(
                            $('<img>').attr('src', Unit.getImage(productionId, CommonMe.getColor()))))
                        .append($('<td>')
                            .html(castleFrom.getProductionTurn() + ' / ' + castleFrom.getProduction()[productionId].time))
                        .append($('<td>')
                            .html(castleFrom.getName())
                            .addClass('button buttonColors')
                            .click(click(castleIdFrom, id)))
                        .append($('<td>')
                            .html($('<img>').attr('src', '/img/game/center.png'))
                            .addClass('iconButton buttonColors')
                            .click(center(castleIdFrom)))
                )
            }
            window
                .append($('<div>').addClass('relocatedProduction').append($('<div>').html(translations.relocatingFrom).addClass('title')).append(relocatingFrom))
        }

        Message.setOverflowHeight(id)

        $('.production .unit .checked').css({
            background: '#222',
            color: '#fff'
        })
        $('.production .unit .checked .attributes span').css({
            color: 'yellow'
        })


        $('.production .unit').click(function (e) {
            $(this).addClass('select')
            $('td:not(#' + $(this).attr('id') + ').unit').removeClass('select')

            if (CommonMe.getCastles().count() > 1) {
                $('.showCastle #relocation').removeClass('buttonOff')
            }

            if (castle.getProductionId() == $(this).attr('id')) {
                $('.showCastle #go').addClass('buttonOff')
            } else {
                $('.showCastle #go').removeClass('buttonOff')
            }
        })

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
                Message.ok(id, WebSocketSend.resurrection)
                Message.cancel(id)
            })

            $('#heroHire:not(.buttonOff)').click(function () {
                var id = Message.show(translations.hireHero, $('<div>').html(translations.doYouWantToHireNewHeroFor1000Gold))
                Message.ok(id, WebSocketSend.hire)
                Message.cancel(id)
            })
        }

        for (var unitId in castle.getProduction()) {
            var unitScene = new UnitScene()
            unitScene.init(50, 50)

            UnitModels.addUnit(castle.getBackgroundColor(), Unit.getName(unitId), unitScene)
            Renderers.add('unit' + unitId, unitScene)
        }
    }
    this.raze = function () {
        if (!CommonMe.getSelectedArmyId()) {
            return;
        }
        var id = Message.show(translations.destroyCastle, $('<div>').html(translations.areYouSure))
        Message.ok(id, WebSocketSend.raze);
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
            Message.ok(id, WebSocketSend.defense);
        }
        Message.cancel(id)
    }
}