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
            j = 0,
            productionUnitTD = [],
            castleWindow = $('<div>').addClass('showCastle'),
            createProductionTD = function() {
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

                    var unitHeader = $('<td>')
                            .addClass('unit' + checked)
                            .attr('id', unitId)
                            .append(
                                $('<div>').addClass('name').html('<b>' + unitName + '</b>' + '<br/> (' + travelBy + ')')
                            ),
                        normal = unitHeader.clone(),
                        event = unitHeader.clone()
                        
                    normal.append($('<div>').addClass('attributes')
                        .append($('<p>').html(translations.productionTime + ': ' + time + '<span>' + castle.getProduction()[unitId].time + '</span>&nbsp;' + translations.turn))
                        .append($('<p>').html(translations.costOfLiving + ': ' + '<span>' + unit.cost + '</span>&nbsp;' + translations.gold + '/' + translations.turn))
                        .append($('<p>').html(translations.movementPoints + ': ' + '<span>' + unit.moves + '</span>'))
                        .append($('<p>').html(translations.attackPoints + ': ' + '<span>' + unit.a + '</span>'))
                        .append($('<p>').html(translations.defencePoints + ': ' + '<span>' + unit.d + '</span>'))
                    )

                    var stopButtonOff = ' buttonOff',
                        relocationButtonOff = ' buttonOff'
                        
                    if (castle.getProductionId()) {
                        if (CommonMe.countCastles() > 1) {
                            var relocationButtonOff = ''
                        }
                        var stopButtonOff = ''
                    }

                    if (castle.getProductionId() && castle.getRelocationCastleId() && CommonMe.getCastles().has(castle.getRelocationCastleId())) {
                        event
                            .append(
                                $('<div>')
                                    .html(translations.relocationTo + ' ' + CommonMe.getCastle(castle.getRelocationCastleId()).getName())
                                    .addClass('button buttonColors')
                                    .click(function () {
                                        CastleWindow.show(CommonMe.getCastle(castle.getRelocationCastleId()))
                                        Message.remove(id)
                                    }))
                            .append(
                                $('<div>')
                                    .html(translations.stop)
                                    .addClass('button buttonColors' + stopButtonOff)
                                    .attr('id', 'stop')
                                    .click(function () {
                                        if ($('.unit.checked').attr('id')) {
                                            castle.handle($('.unit.checked').attr('id'), 1, 0)
                                            Message.remove(id)
                                        }
                                    }))
                            .append(
                                $('<div>')
                                    .html(translations.relocation)
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
                            .append(
                                $('<div>')
                                    .addClass('button buttonColors buttonOff')
                                    .attr('id', 'go')
                                    .html(translations.start)
                                    .click(function () {
                                        if ($('.unit.select').attr('id')) {
                                            castle.handle($('.unit.select').attr('id'))
                                            Message.remove(id)
                                        }
                                    }))
                    }
                    
                    productionUnitTD[j] = {'normal': normal, 'event': event}
                    j++;
                }
            }
            
        createProductionTD()
        var k = Math.ceil(j / 2)
        for (l = 0; l < k; l++) {
            var tr = $('<tr>')
            var m = l * 2
            tr.append(productionUnitTD[m].normal)
            if (isSet(productionUnitTD[m + 1])) {
                tr.append(productionUnitTD[m + 1].normal)
            }
            table.append(tr)
        }

        // stop & relocation

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
                                    Message.remove(id)
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
            var id = Message.show(translations.capitalCity + '&nbsp;' + castle.getName(), castleWindow)
        } else {
            var id = Message.show(translations.castle + '&nbsp;' + castle.getName(), castleWindow)
        }

        if (castle.getCapital()) {
            castleWindow
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
            castleWindow
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


        $('.production .unit').click(function () {
            for (var i in productionUnitTD) {
                if ($(this).attr('id') == productionUnitTD[i].normal.attr('id')) {
                    $(this).html(productionUnitTD[i].event.html())
                }
            }
//             $(this).addClass('select')
//             $('td:not(#' + $(this).attr('id') + ').unit').removeClass('select')
//             if (CommonMe.getCastles().count() > 1) {
//                 $('.showCastle #relocation').removeClass('buttonOff')
//             }
//             if (castle.getProductionId() == $(this).attr('id')) {
//                 $('.showCastle #go').addClass('buttonOff')
//             } else {
//                 $('.showCastle #go').removeClass('buttonOff')
//             }
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
                Message.ok(id, WebSocketSendCommon.resurrection)
                Message.cancel(id)
            })

            $('#heroHire:not(.buttonOff)').click(function () {
                var id = Message.show(translations.hireHero, $('<div>').html(translations.doYouWantToHireNewHeroFor1000Gold))
                Message.ok(id, WebSocketSendCommon.hire)
                Message.cancel(id)
            })
        }

        // for (var unitId in castle.getProduction()) {
        //     var unitScene = new UnitScene()
        //     unitScene.init(50, 50)
        //
        //     UnitModels.addUnit(castle.getBackgroundColor(), Unit.getName(unitId), unitScene)
        //     Renderers.add('unit' + unitId, unitScene)
        // }
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
