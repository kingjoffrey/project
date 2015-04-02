/**
 * Created by bartek on 31.03.15.
 */
var CastleWindow = new function () {
    var center = function (i) {
            return function () {
                Zoom.lens.setcenter(Me.getCastle(i).getX(), Me.getCastle(i).getY())
            }
        },
        click = function (i) {
            return function () {
                CastleWindow.show(Me.getCastle(i))
            }
        }


    this.show = function (castle) {
        var time = '',
            attr,
            capital = '',
            table = $('<table>'),
            info = $('<table>'),
            j = 0,
            td = new Array(),
            window = $('<div>').addClass('showCastle')

        for (var unitId in castle.getProduction()) {
            var travelBy = '',
                unit = Units.get(unitId)

            if (unitId == castle.getProductionId()) {
                attr = {
                    type: 'radio',
                    name: 'production',
                    value: unitId,
                    checked: 'checked'
                }
                time = castle.getProductionTurn() + '/';
            } else {
                attr = {
                    type: 'radio',
                    name: 'production',
                    value: unitId
                }
                time = '';
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
                .addClass('unit')
                .attr('id', unitId)
                .append($('<div>')
                    .append($('<input>').attr(attr))
                    .append($('<div>')
                        .html('<b>' + name + '</b>' + '<br/> (' + travelBy + ')')
                        .addClass('name'))
                    .addClass('top'))
                .append($('<div>')
                    .append($('<img>').attr('src', Unit.getImage(unitId, Me.getColor())))
                    .addClass('img'))
                .append($('<div>')
                    .addClass('attributes')
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
            if (Me.countCastles() > 1) {
                var relocationButtonOff = ''
            }
            var stopButtonOff = ''
        }

        info
            .append($('<tr>')
                .append($('<td>').append(translations.castleDefense + ': '))
                .append($('<td>').append(castle.getDefense())))
            .append($('<tr>')
                .append($('<td>').append(translations.incomeFromCastle + ': '))
                .append($('<td>').append(castle.getIncome() + ' ' + translations.gold_turn)))
        window
            .append(info)
            .append($('<div>').addClass('production').append($('<div>').html(translations.availableUnits).addClass('title')).append(table).attr('id', castle.getCastleId()))
            .append($('<div>')
                .html(translations.stopProduction)
                .addClass('button buttonColors' + stopButtonOff)
                .attr('id', 'stop')
                .click(function () {
                    if ($('input:radio[name=production]:checked').val()) {
                        castle.handle(1, 0)
                    }
                }))
            .append($('<div>')
                .html(translations.productionRelocation)
                .addClass('button buttonColors' + relocationButtonOff)
                .attr('id', 'relocation')
                .click(function () {
                    if ($('input:radio[name=production]:checked').val()) {
                        castle.handle(0, 1)
                    }
                }))
            .append($('<div>')
                .html($('<img>').attr({src: '/img/game/center.png'}))
                .addClass('iconButton buttonColors')
                .click(center(castle.getCastleId()))
                .attr('id', 'center'))
            .append($('<div>')
                .addClass('button buttonColors buttonOff')
                .attr('id', 'go')
                .html(translations.startProduction)
                .click(function () {
                    castle.handle()
                    Message.remove()
                }))
            .append($('<div>')
                .addClass('button buttonColors cancel')
                .html(translations.close)
                .click(function () {
                    Message.remove()
                }))

        // relocation to

        if (castle.getRelocationCastleId() && Me.getCastles().has(castle.getRelocationCastleId())) {
            window
                .append($('<div>').addClass('relocatedProduction').append($('<div>').html(translations.relocatingTo).addClass('title')).append(
                    $('<table>').append(
                        $('<tr>')
                            .append(
                            $('<td>').append($('<img>').attr('src', Unit.getImage(castle.getProductionId(), Me.getColor())))
                        )
                            .append(
                            $('<td>')
                                .html(castle.getProductionTurn() + ' / ' + castle.getProduction()[castle.getProductionId()].time)
                        )
                            .append(
                            $('<td>')
                                .html(Me.getCastle(castle.getRelocationCastleId()).getName())
                                .addClass('button buttonColors')
                                .click(function () {
                                    CastleWindow.show(Me.getCastle(castle.getRelocationCastleId()))
                                })
                        )
                            .append(
                            $('<td>')
                                .html($('<img>').attr('src', '/img/game/center.png'))
                                .addClass('iconButton buttonColors')
                                .click(center(castle.getRelocationCastleId()))
                        )
                    )
                ))
        }

        // relocation from

        var relocatedProduction = Me.getCastles().getRelocatedProduction(castle.getCastleId())
        if (relocatedProduction.length) {
            var relocatingFrom = $('<table>')

            for (var i in relocatedProduction) {
                var castleIdFrom = relocatedProduction[i],
                    castleFrom = Me.getCastle(castleIdFrom),
                    productionId = castleFrom.getProductionId()

                relocatingFrom.append(
                    $('<tr>')
                        .append(
                        $('<td>').append(
                            $('<img>').attr('src', Unit.getImage(productionId, Me.getColor()))
                        )
                    )
                        .append(
                        $('<td>')
                            .html(castleFrom.getProductionTurn() + ' / ' + castleFrom.getProduction()[productionId].time)
                    )
                        .append(
                        $('<td>')
                            .html(castleFrom.getName())
                            .addClass('button buttonColors')
                            .click(click(castleIdFrom))
                    )
                        .append(
                        $('<td>')
                            .html($('<img>').attr('src', '/img/game/center.png'))
                            .addClass('iconButton buttonColors')
                            .click(center(castleIdFrom))
                    )
                )
            }
            window
                .append($('<div>').addClass('relocatedProduction').append($('<div>').html(translations.relocatingFrom).addClass('title')).append(relocatingFrom))
        }

        if (castle.getCapital()) {
            var id = Message.show(translations.capitalCity + '&nbsp;' + castle.getName(), window)
        } else {
            var id = Message.show(translations.castle + '&nbsp;' + castle.getName(), window)
        }

        Message.setOverflowHeight(id)

        $('.production .unit input[type=radio]:checked').parent().parent().css({
            background: 'url(/img/bg_1.jpg)',
            color: '#fff'
        })
        $('.production .unit input[type=radio]:checked').parent().parent().find('.attributes span').css({
            color: 'yellow'
        })

        //if (castle.getProductionId()) {
        //    $('.showCastle #go').removeClass('buttonOff')
        //}

        // unit click

        $('.production .unit').click(function (e) {
            $('.production .unit :radio').each(function () {
                $(this).prop('checked', false).parent().parent().css({
                    background: '#fff',
                    color: '#000'
                })
            })

            $('td#' + $(this).attr('id') + '.unit input').prop('checked', true).parent().parent().css({
                background: 'url(/img/bg_1.jpg)',
                color: '#fff'
            })

            $('td#' + $(this).attr('id') + '.unit .attributes span').css({
                color: 'yellow'
            })

            $('td:not(#' + $(this).attr('id') + ').unit .attributes span').css({
                color: '#000'
            })

            if (Me.getCastles().count() > 1) {
                $('.showCastle #relocation').removeClass('buttonOff')
            }

            if (castle.getProductionId() == $(this).attr('id')) {
                $('.showCastle #go').addClass('buttonOff')
            } else {
                $('.showCastle #go').removeClass('buttonOff')
            }
        })

        //$('.relocatedProduction').css({
        //    width: parseInt($('.production').css('width')) + 'px'
        //})
    }
    this.raze = function () {
        if (!Me.getSelectedArmyId()) {
            return;
        }
        var id = Message.show(translations.destroyCastle, $('<div>').html(translations.areYouSure))
        Message.ok(id, Websocket.raze);
        Message.cancel(id)
    }
    this.build = function () {
        if (!Me.getSelectedArmyId()) {
            return;
        }

        var army = Me.getArmy(Me.getSelectedArmyId())
        var castle = Me.getCastle(Fields.get(army.getX(), army.getY()).getCastleId())

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
            Message.ok(id, Websocket.defense);
        }
        Message.cancel(id)
    }
}