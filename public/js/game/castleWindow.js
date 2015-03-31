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

        if (castle.getCapital()) {
            capital = ' - ' + translations.capitalCity
        }

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
                    .append($('<p>').html(translations.productionTime + ':&nbsp;' + time + '<span>' + castle.getProduction()[unitId].time + '</span>&nbsp;' + translations.turn))
                    .append($('<p>').html(translations.costOfLiving + ':&nbsp;' + '<span>' + unit.cost + '</span>&nbsp;' + translations.gold + '/' + translations.turn))
                    .append($('<p>').html(translations.movementPoints + ':&nbsp;' + '<span>' + unit.moves + '</span>'))
                    .append($('<p>').html(translations.attackPoints + ':&nbsp;' + '<span>' + unit.a + '</span>'))
                    .append($('<p>').html(translations.defencePoints + ':&nbsp;' + '<span>' + unit.d + '</span>')))
            j++;
        }
        var k = Math.ceil(j / 2);
        for (l = 0; l < k; l++) {
            var tr = $('<tr>');
            var m = l * 2;
            tr.append(td[m]);
            if (typeof td[m + 1] == 'undefined') {
                tr.append($('<td>').addClass('empty').html('&nbsp;'));
            } else {
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

        window
            .append($('<div>')
                .html($('<img>').attr({src: '/img/game/center.png'}))
                .addClass('iconButton buttonColors')
                .click(center(castle.getCastleId()))
                .attr('id', 'center'))
            .append($('<h4>').append(translations.capital))
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
                .addClass('button buttonColors')
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
                                    Message.castle(Me.getCastle(castle.getRelocationCastleId()))
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

        Message.show(translations.castle + '&nbsp;' + castle.getName(), window);
        //Message.setOverflowHeight(id)

        $('.production .unit input[type=radio]:checked').parent().parent().css({
            background: 'url(/img/bg_1.jpg)',
            color: '#fff'
        })
        $('.production .unit input[type=radio]:checked').parent().parent().find('.attributes span').css({
            color: 'yellow'
        })

        // click

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
            console.log(castle.getProductionId())
            console.log($(this).attr('id'))
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
}