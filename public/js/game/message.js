var Message = {
    drag: 0,
    x: 0,
    y: 0,
    element: function () {
        return $('#goldBox');
    },
    remove: function (id) {
        if (isSet(id)) {
            $('#' + id).fadeOut(200, function () {
                this.remove();
            })
        } else {
            if (notSet($('.message'))) {
                return;
            }
            $('.message').remove();
        }
    },
    show: function (title, txt) {
        this.remove()
        var id = makeId(10)
        this.element().after(
            $('<div>')
                .addClass('message box')
                .append($('<h3>').html(title))
                .append($(txt).addClass('overflow'))
                .attr('id', id)
                .fadeIn(200)
//                .mousedown(function (e) {
//                    Message.drag = 1
//                    Message.x = e.pageX - parseInt($(this).css('left'))
//                    Message.y = e.pageY - parseInt($(this).css('top'))
//                    $(this).css('cursor', 'url(/img/game/cursor_hand_drag.png) 15 15, default')
//                })
//                .mouseup(function () {
//                    Message.drag = 0
//                    $(this).css('cursor', 'url(/img/game/cursor_hand.png) 15 15, default')
//                })
//                .mouseout(function () {
//                    if (Message.drag) {
//                        return
//                    }
//                    Message.drag = 0
//                    $(this).css('cursor', 'url(/img/game/cursor_hand.png) 15 15, default')
//                })
//                .mousemove(function (e) {
//                    if (Message.drag == 0) {
//                        return
//                    }
//                    var top = e.pageY - Message.y
//                    var left = e.pageX - Message.x
//                    $(this).css({
//                        top: top,
//                        left: left
//                    })
//                })
        )
        this.adjust(id)
        return id
    },
    adjust: function (id) {
        if (isSet(id)) {
            var maxHeight = gameHeight - 120
            if (maxHeight < parseInt($('#' + id).css('min-height'))) {
                maxHeight = parseInt($('#' + id).css('min-height'))
            }
            var maxWidth = gameWidth - 600
            if (maxWidth < parseInt($('#' + id).css('min-width'))) {
                maxWidth = parseInt($('#' + id).css('min-width'))
            }
            $('#' + id)
                .css({
                    'max-width': maxWidth + 'px',
                    'max-height': maxHeight + 'px'
                })
//                .jScrollPane()
            var left = gameWidth / 2 - $('#' + id).outerWidth() / 2;
            $('#' + id)
                .css({
                    left: left + 'px'
                })
        } else if ($('.message').length) {
            var maxHeight = gameHeight - 120
            if (maxHeight < parseInt($('.message').css('min-height'))) {
                console.log(maxHeight)
                maxHeight = parseInt($('.message').css('min-height'))
            }
            var maxWidth = gameWidth - 600
            if (maxWidth < parseInt($('.message').css('min-width'))) {
                maxWidth = parseInt($('.message').css('min-width'))
            }
            $('.message')
                .css({
                    'max-width': maxWidth + 'px',
                    'max-height': maxHeight + 'px'
                })
//                .jScrollPane()
            $('.message')
            var left = gameWidth / 2 - $('.message').outerWidth() / 2;
            $('.message')
                .css({
                    left: left + 'px'
                })
        }
    },
    setOverflowHeight: function (id) {
        if ($('.showCastle').length) {
            var minus = 70
        } else {
            var minus = 90
        }
        if (isSet(id)) {
            var height = parseInt($('#' + id).css('height')) - minus;
            $('#' + id + ' div.overflow').css('height', height + 'px')
        } else {
            var height = parseInt($('.message').css('height')) - minus;
            $('.message' + ' div.overflow').css('height', height + 'px')
        }
        if (Army.isSelected) {
            Army.isSelected = 0
        }
    },
    ok: function (id, func) {
        $('#' + id).append(
            $('<div>')
                .addClass('button buttonColors go')
                .html(translations.ok)
                .click(function () {
                    if (isSet(func)) {
                        func();
                    }
                    Message.remove(id);
                })
        );

        this.setOverflowHeight(id)
    },
    cancel: function (id, func) {
        $('#' + id).append(
            $('<div>')
                .addClass('button buttonColors cancel')
                .html(translations.cancel)
                .click(function () {
                    if (isSet(func)) {
                        func();
                    }
                    Message.remove(id);
                })
        )
    },
    simple: function (title, message) {
        var id = this.show(title, $('<div>').html(message).addClass('simple'));
        this.ok(id)
        return id
    },
    error: function (message) {
        Sound.play('error');
        var div = $('<div>').html(message).addClass('error')
        this.simple(translations.error, div);
    },
    surrender: function () {
        var id = this.simple(translations.surrender, translations.areYouSure)
        this.ok(id, Websocket.surrender);
        this.cancel(id)
    },
    turn: function () {
        this.remove();
        if (my.turn && Turn.number == 1 && castles[firstCastleId].currentProductionId === null) {
            Message.castle(firstCastleId);
        } else {
            var id = this.simple(translations.yourTurn, translations.thisIsYourTurnNow)
        }
    },
    castle: function (castleId) {
        if (notSet(castles[castleId])) {
            return;
        }

        var time = '',
            attr,
            capital = '',
            table = $('<table>'),
            j = 0,
            td = new Array()

        if (castles[castleId].capital) {
            capital = ' - ' + translations.capitalCity;
        }

        for (unitId in castles[castleId].production) {
            var travelBy = '';
            if (unitId == castles[castleId].currentProductionId) {
                attr = {
                    type: 'radio',
                    name: 'production',
                    value: unitId,
                    checked: 'checked'
                }
                time = castles[castleId].currentProductionTurn + '/';
            } else {
                attr = {
                    type: 'radio',
                    name: 'production',
                    value: unitId
                }
                time = '';
            }

            if (units[unitId].canFly) {
                travelBy = translations.ground + ' / ' + translations.air;
            } else if (units[unitId].canSwim) {
                travelBy = translations.water;
            } else {
                travelBy = translations.ground;
            }
            if (units[unitId].name_lang) {
                var name = units[unitId].name_lang;
            } else {
                var name = units[unitId].name;
            }
            td[j] = $('<td>')
                .addClass('unit')
                .attr('id', unitId)
                .append(
                $('<div>')
                    .append($('<input>').attr(attr))
                    .append(
                    $('<div>')
                        .html(name + '<br/> (' + travelBy + ')')
                        .addClass('name')
                )
                    .addClass('top')
            )
                .append(
                $('<div>')
                    .append($('<img>').attr('src', Unit.getImage(unitId, my.color)))
                    .addClass('img')
            )
                .append(
                $('<div>')
                    .addClass('attributes')
                    .append($('<p>').html(translations.time + ':&nbsp;' + time + castles[castleId].production[unitId].time + ' ' + translations.turn))
                    .append($('<p>').html(translations.cost + ':&nbsp;' + units[unitId].cost + ' ' + translations.gold))
                    .append($('<p>').html(translations.moves + '&nbsp;' + units[unitId].numberOfMoves + '&nbsp;/&nbsp;' + translations.attack + '&nbsp;' + units[unitId].attackPoints + '&nbsp;/&nbsp;' + translations.defence + '&nbsp;' + units[unitId].defensePoints))
            );
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
        if (castles[castleId].currentProductionId) {
            if (Castle.countMyCastles() > 1) {
                var relocationButtonOff = ''
            }
            var stopButtonOff = ''
        }

        table.append(
            $('<tr>')
                .append(
                $('<td>')
                    .html(
                    $('<a>')
                        .html(translations.stopProduction)
                        .addClass('button buttonColors' + stopButtonOff)
                        .attr('id', 'stop')
                        .click(function () {
                            if ($('input:radio[name=production]:checked').val()) {
                                Castle.handle(1, 0)
                            }
                        })
                )
            )
                .append(
                $('<td>')
                    .html(
                    $('<a>')
                        .html(translations.productionRelocation)
                        .addClass('button buttonColors' + relocationButtonOff)
                        .attr('id', 'relocation')
                        .click(function () {
                            if ($('input:radio[name=production]:checked').val()) {
                                Castle.handle(0, 1)
                            }
                        })
                )
            )
        );

        var center = function (i) {
            return function () {
                zoomer.lensSetCenter(castles[i].x * 40, castles[i].y * 40)
            }
        }

        var div = $('<div>')
            .addClass('showCastle')
            .append(
            $('<div>')
                .html(
                $('<img>')
                    .attr({
                        src: '/img/game/center.png',
                        width: '23px',
                        height: '23px'
                    })
            )
                .addClass('iconButton buttonColors')
                .click(center(castleId))
                .attr('id', 'center')
        )
            .append($('<h3>').append(castles[castleId].name).append(capital).addClass('name'))
            .append($('<h5>').append(translations.castleDefense + ': ' + castles[castleId].defense))
            .append($('<h5>').append(translations.income + ': ' + castles[castleId].income + ' ' + translations.gold_turn))
            .append($('<br>'))
            .append($('<fieldset>').addClass('production').append($('<label>').html(translations.production)).append(table).attr('id', castleId))

        // relocation to

        if (castles[castleId].color == my.color && castles[castleId].relocationToCastleId && castles[castleId].currentProductionId) {
            div
                .append($('<br>'))
                .append($('<fieldset>').addClass('relocatedProduction').append($('<label>').html(translations.relocatingTo)).append(
                    $('<table>').append(
                        $('<tr>')
                            .append(
                            $('<td>').append($('<img>').attr('src', Unit.getImage(castles[castleId].currentProductionId, my.color)))
                        )
                            .append(
                            $('<td>')
                                .html(castles[castleId].currentProductionTurn + ' / ' + castles[castleId].production[castles[castleId].currentProductionId].time)
                        )
                            .append(
                            $('<td>')
                                .html(castles[castles[castleId].relocationToCastleId].name)
                                .addClass('button buttonColors')
                                .click(function () {
                                    Message.castle(castles[castleId].relocationToCastleId)
                                })
                        )
                            .append(
                            $('<td>')
                                .html($('<img>').attr('src', '/img/game/center.png'))
                                .addClass('iconButton buttonColors')
                                .click(center(castles[castleId].relocationToCastleId))
                        )
                    )
                ))
        }

        // relocation from

        if (isSet(castles[castleId].relocatedProduction) && !$.isEmptyObject(castles[castleId].relocatedProduction)) {
            var relocatingFrom = $('<table>'),
                click = function (i) {
                    return function () {
                        Message.castle(i)
                    }
                }

            for (castleIdFrom in castles[castleId].relocatedProduction) {
                var currentProductionId = castles[castleIdFrom].currentProductionId
                relocatingFrom.append(
                    $('<tr>')
                        .append(
                        $('<td>').append(
                            $('<img>').attr('src', Unit.getImage(currentProductionId, my.color))
                        )
                    )
                        .append(
                        $('<td>')
                            .html(castles[castleIdFrom].currentProductionTurn + ' / ' + castles[castleIdFrom].production[currentProductionId].time)
                    )
                        .append(
                        $('<td>')
                            .html(castles[castleIdFrom].name)
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
            div
                .append($('<br>'))
                .append($('<fieldset>').addClass('relocatedProduction').append($('<label>').html(translations.relocatingFrom)).append(relocatingFrom))
        }

        var id = this.show('', div);
        this.ok(id, Castle.handle);
        this.cancel(id)

        $('.production .unit input[type=radio]:checked').parent().parent().css({
            background: 'url(/img/bg_1.jpg)',
            color: '#fff'
        })

        // click

        $('.production .unit').click(function (e) {
            $('.production .unit :radio').each(function () {
                $(this)
                    .prop('checked', false)
                    .parent().parent().css({
                        background: '#fff',
                        color: '#000'
                    })
            })

            $('td#' + $(this).attr('id') + '.unit input')
                .prop('checked', true)
                .parent().parent().css({
                    background: 'url(/img/bg_1.jpg)',
                    color: '#fff'
                })

            if (Castle.countMyCastles() > 1) {
                $('.production #relocation').removeClass('buttonOff')
            }
        })

        $('.relocatedProduction').css({
            width: parseInt($('.production').css('width')) + 'px'
        })

    },
    nextTurn: function () {
        var id = this.show(translations.nextTurn, $('<div>').html(translations.areYouSure))
        this.ok(id, Websocket.nextTurn);
        this.cancel(id)
    },
    disband: function () {
        var id = this.show(translations.disbandArmy, $('<div>').html(translations.areYouSure))
        this.ok(id, Websocket.disband);
        this.cancel(id)
    },
    split: function (a) {
        if (notSet(Army.selected)) {
            return;
        }

        var div = $('<div>')
            .addClass('split')
            .append(
            $('<div>')
                .html(
                $('<input>')
                    .attr({
                        type: 'checkbox'
                    })
                    .change(function () {
                        $('.message .row input').each(function () {
                            if ($(this).is(':checked')) {
                                $(this).prop('checked', false)
                            } else {
                                $(this).prop('checked', true)
                            }
                        })
                    })
            )
                .attr('id', 'selectAll')
        )
        var numberOfUnits = 0;

        for (i in Army.selected.soldiers) {
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Unit.getImage(Army.selected.soldiers[i].unitId, Army.selected.color),
                            'id': 'unit' + Army.selected.soldiers[i].soldierId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + Army.selected.soldiers[i].movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'soldierId',
                        value: Army.selected.soldiers[i].soldierId
                    })))
            );
        }

        for (i in Army.selected.heroes) {
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Hero.getImage(Army.selected.color),
                            'id': 'hero' + Army.selected.heroes[i].heroId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + Army.selected.heroes[i].movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'heroId',
                        value: Army.selected.heroes[i].heroId
                    })))
            );
        }

        var id = this.show(translations.split, div);
        this.ok(id, Websocket.split);
        this.cancel(id)

    },
    armyStatus: function () {
        if (notSet(Army.selected)) {
            return;
        }

        var div = $('<div>')
            .addClass('status')
        numberOfUnits = 0,
            bonusTower = 0,
            castleDefense = getMyCastleDefenseFromPosition(Army.selected.x, Army.selected.y),
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
                zoomer.lensSetCenter(castles[i].x * 40, castles[i].y * 40)
            }
        }

        if (Tower.isAtPosition(Army.selected.x, Army.selected.y)) {
            bonusTower = 1;
        }

        if (Army.selected.flyBonus) {
            attackFlyBonus.html(' +1').addClass('value plus')
            defenseFlyBonus.html(' +1').addClass('value plus')
        }
        if (Army.selected.heroKey) {
            attackHeroBonus.html(' +1').addClass('value plus')
            defenseHeroBonus.html(' +1').addClass('value plus')
        }
        if (bonusTower) {
            defenseTowerBonus.html(' +1').addClass('value plus')
        }
        if (castleDefense) {
            defenseCastleBonus.html(' +' + castleDefense).addClass('value plus')
        }

        for (i in Army.selected.soldiers) {
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append(
                    $('<div>')
                        .addClass('rowContent')
                        .append($('<div>').addClass('nr').html(numberOfUnits))
                        .append($('<div>').addClass('img').html(
                            $('<img>').attr({
                                'src': Unit.getImage(Army.selected.soldiers[i].unitId, Army.selected.color),
                                'id': 'unit' + Army.selected.soldiers[i].soldierId
                            })
                        ))
                        .append(
                        $('<table>')
                            .addClass('leftTable')
                            .append(
                            $('<tr>')
                                .append($('<td>').html(translations.currentMoves + ': '))
                                .append($('<td>').html(Army.selected.soldiers[i].movesLeft).addClass('value'))
                        )
                            .append(
                            $('<tr>')
                                .append($('<td>').html(translations.defaultMoves + ': '))
                                .append($('<td>').html(units[Army.selected.soldiers[i].unitId].numberOfMoves).addClass('value'))
                        )
                            .append(
                            $('<tr>')
                                .append($('<td>').html(translations.attackPoints + ': '))
                                .append(
                                $('<td>')
                                    .append($('<div>').html(units[Army.selected.soldiers[i].unitId].attackPoints))
                                    .append(attackFlyBonus.clone())
                                    .append(attackHeroBonus.clone())
                                    .addClass('value')
                            )
                        )
                            .append(
                            $('<tr>')
                                .append($('<td>').html(translations.defencePoints + ': '))
                                .append(
                                $('<td>')
                                    .append($('<div>').html(units[Army.selected.soldiers[i].unitId].defensePoints))
                                    .append(defenseFlyBonus.clone())
                                    .append(defenseHeroBonus.clone())
                                    .append(defenseTowerBonus.clone())
                                    .append(defenseCastleBonus.clone())
                                    .addClass('value')
                            )
                        )
                    )
                        .append(
                        $('<table>')
                            .addClass('rightTable')
                            .append(
                            $('<tr>')
                                .append($('<td>').html(translations.movementCostThroughTheForest + ': '))
                                .append($('<td>').html(units[Army.selected.soldiers[i].unitId].f).addClass('value'))
                        )
                            .append(
                            $('<tr>')
                                .append($('<td>').html(translations.movementCostThroughTheSwamp + ': '))
                                .append($('<p>').html(units[Army.selected.soldiers[i].unitId].s).addClass('value')))
                            .append(
                            $('<tr>')
                                .append($('<td>').html(translations.movementCostThroughTheHills + ': '))
                                .append($('<p>').html(units[Army.selected.soldiers[i].unitId].m).addClass('value'))
                        )
                    )
                )
            );
        }

        for (i in Army.selected.heroes) {
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append(
                    $('<div>')
                        .addClass('rowContent')
                        .append($('<div>').addClass('nr').html(numberOfUnits))
                        .append($('<div>').addClass('img').html(
                            $('<img>').attr({
                                'src': Hero.getImage(Army.selected.color),
                                'id': 'hero' + Army.selected.heroes[i].heroId
                            })
                        ))
                        .append(
                        $('<table>').addClass('leftTable')
                            .append(
                            $('<tr>')
                                .append($('<td>').html('Current moves: '))
                                .append($('<td>').html(Army.selected.heroes[i].movesLeft).addClass('value'))
                        )
                            .append(
                            $('<tr>')
                                .append($('<td>').html('Default moves: '))
                                .append($('<td>').html(Army.selected.heroes[i].numberOfMoves).addClass('value'))
                        )
                            .append(
                            $('<tr>')
                                .append($('<td>').html('Attack points: '))
                                .append(
                                $('<td>')
                                    .append($('<div>').html(Army.selected.heroes[i].attackPoints))
                                    .append(attackFlyBonus.clone())
//                                                    .append(attackHeroBonus.clone())
                                    .addClass('value')
                            )
                        )
                            .append(
                            $('<tr>')
                                .append($('<td>').html('Defense points: '))
                                .append(
                                $('<td>')
                                    .append($('<div>').html(Army.selected.heroes[i].defensePoints))
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

        var id = this.show(translations.status, div);
        this.ok(id)
    },
    battle: function (r) {
        var newBattle = new Array();

        var attack = $('<div>').addClass('battle attack');

        for (i in r.battle.attack.soldiers) {
            if (r.battle.attack.soldiers[i].succession) {
                newBattle[r.battle.attack.soldiers[i].succession] = {
                    'soldierId': r.battle.attack.soldiers[i].soldierId
                };
            }
            attack.append(
                $('<div>')
                    .attr('id', 'unit' + r.battle.attack.soldiers[i].soldierId)
                    .css('background', 'url(' + Unit.getImage(r.battle.attack.soldiers[i].unitId, r.attackerColor) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }
        for (i in r.battle.attack.heroes) {
            if (r.battle.attack.heroes[i].succession) {
                newBattle[r.battle.attack.heroes[i].succession] = {
                    'heroId': r.battle.attack.heroes[i].heroId
                };
            }
            attack.append(
                $('<div>')
                    .attr('id', 'hero' + r.battle.attack.heroes[i].heroId)
                    .css('background', 'url(' + Hero.getImage(r.attackerColor) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }

        var attackLayout = $('<div>')
            .append(attack)
            .append($('<div>').html(players[r.attackerColor].longName + ' (' + translations.attack + ')'))

        var defense = $('<div>').addClass('battle defense');

        for (i in r.battle.defense.soldiers) {
            if (r.battle.defense.soldiers[i].succession) {
                newBattle[r.battle.defense.soldiers[i].succession] = {
                    'soldierId': r.battle.defense.soldiers[i].soldierId
                };
            }
            defense.append(
                $('<div>')
                    .attr('id', 'unit' + r.battle.defense.soldiers[i].soldierId)
                    .css('background', 'url(' + Unit.getImage(r.battle.defense.soldiers[i].unitId, r.defenderColor) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }

        for (i in r.battle.defense.heroes) {
            if (r.battle.defense.heroes[i].succession) {
                newBattle[r.battle.defense.heroes[i].succession] = {
                    'heroId': r.battle.defense.heroes[i].heroId
                };
            }
            defense.append(
                $('<div>')
                    .attr('id', 'hero' + r.battle.defense.heroes[i].heroId)
                    .css('background', 'url(' + Hero.getImage(r.defenderColor) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }

        if (isSet(players[r.defenderColor])) {
            var longName = players[r.defenderColor].longName
        } else {
            var longName = 'Shadow'
        }

        var defenseLayout = $('<div>')
            .append($('<div>').html(longName + ' (' + translations.defence + ')'))

        if (isDigit(r.castleId)) {
            defenseLayout.append(
                $('<div>')
                    .addClass('castle')
                    .css({
                        position: 'static',
                        background: 'url(/img/game/castles/' + r.defenderColor + '.png) center center no-repeat',
                        margin: '0 auto'
                    })
            )
        }

        if (isSet(r.defenderArmy[0]) && r.defenderColor != 'neutral' && Tower.isAtPosition(players[r.defenderColor].armies[r.defenderArmy[0].armyId].x, players[r.defenderColor].armies[r.defenderArmy[0].armyId].y)) {
            defenseLayout.append(
                $('<div>')
                    .addClass('tower')
                    .css({
                        position: 'static',
                        background: 'url(/img/game/towers/' + r.defenderColor + '.png) center center no-repeat',
                        margin: '0 auto'
                    })
            )
        }

        defenseLayout.append(defense)

        var div = $('<div>')
            .append($('<p>').html('&nbsp;'))
            .append(
            $('<div>')
                .addClass('grass')
                .append(defenseLayout)
                .append($('<p>').html('&nbsp;'))
                .append(attackLayout)
        )

        var id = this.show(translations.battle, div);
        if (!players[r.attackerColor].computer) {
            this.ok(id)// add Move.end(r)
        } else {
            this.ok(id)
        }

        $('.go').css('display', 'none')

        if (newBattle) {
            if (players[r.attackerColor].computer) {
                Message.kill(newBattle, r);
            } else {
                setTimeout(function () {
                    Message.kill(newBattle, r);
                }, 2500);
            }
        }
    },
    kill: function (b, r) {
        console.log('kill 0')
        for (i in b) {
            break
        }

        if (notSet(b[i])) {
            if (!players[r.attackerColor].computer) {
                $('.go').fadeIn(100)
            }
            Move.end(r)
            return
        }

        if (isSet(b[i].soldierId)) {
            var unitElement = $('#unit' + b[i].soldierId)
            if (!unitElement.length) {
                Move.end(r)
            }

            unitElement.append($('<div>').addClass('killed'));
            if (!players[r.attackerColor].computer) {
                setTimeout(function () {
                    Sound.play('error');
                }, 500)
            }
            $('#unit' + b[i].soldierId + ' .killed').fadeIn(1000, function () {
                if (r.attackerColor == my.color) {
                    for (k in players[my.color].armies[r.attackerArmy.armyId].soldiers) {
                        if (players[my.color].armies[r.attackerArmy.armyId].soldiers[k].soldierId == b[i].soldierId) {
                            costIncrement(-units[players[my.color].armies[r.attackerArmy.armyId].soldiers[k].unitId].cost)
                        }
                    }
                }

                if (r.defenderColor == my.color) {
                    for (j in r.defenderArmy) {
                        for (k in players[my.color].armies[r.defenderArmy[j].armyId].soldiers) {
                            if (players[my.color].armies[r.defenderArmy[j].armyId].soldiers[k].soldierId == b[i].soldierId) {
                                costIncrement(-units[players[my.color].armies[r.defenderArmy[j].armyId].soldiers[k].unitId].cost)
                            }
                        }
                    }
                }
                delete b[i];
                Message.kill(b, r);
            });
        } else if (isSet(b[i].heroId)) {
            var heroElement = $('#hero' + b[i].heroId)
            if (!heroElement.length) {
                Move.end(r)
            }

            heroElement.append($('<div>').addClass('killed'));
            if (!players[r.attackerColor].computer) {
                setTimeout(function () {
                    Sound.play('error');
                }, 500)
            }
            $('#hero' + b[i].heroId + ' .killed').fadeIn(1000, function () {
                delete b[i];
                Message.kill(b, r);
            });
        }
        console.log('kill 1')
    },
    raze: function () {
        if (Army.selected == null) {
            return;
        }
        var id = this.simple(translations.destroyCastle, translations.areYouSure)
        this.ok(id, Websocket.raze);
        this.cancel(id)
    },
    build: function () {
        if (Army.selected == null) {
            return;
        }

        var castleId = Castle.getMy(Army.selected.x, Army.selected.y);

        var costBuildDefense = 0;
        for (i = 1; i <= castles[castleId].defense; i++) {
            costBuildDefense += i * 100;
        }
        var newDefense = castles[castleId].defense + 1;

        var div = $('<div>')
            .append($('<div>').html(translations.currentDefense + ': ' + castles[castleId].defense))
            .append($('<div>').html(translations.newDefense + ': ' + newDefense))
            .append($('<div>').html(translations.cost + ': ' + costBuildDefense + ' ' + translations.gold))

        var id = this.show(translations.doYouWantToBuildCastleDefense, div);
        this.ok(id, Websocket.defense);
        this.cancel(id)
    },
    statistics: function () {
        var statistics = $('<div>')
        var table = $('<table>')
            .addClass('statistics')
            .append($('<tr>')
                .append($('<th>'))
                .append($('<th>'))
                .append($('<th>').html(translations.castlesHeld))
                .append($('<th>').html(translations.castlesConquered))
                .append($('<th>').html(translations.castlesLost))
                .append($('<th>').html(translations.castlesRazed))
                .append($('<th>').html(translations.unitsCreated))
                .append($('<th>').html(translations.unitsKilled))
                .append($('<th>').html(translations.unitsLost))
                .append($('<th>').html(translations.heroesKilled))
                .append($('<th>').html(translations.heroesLost))
        );
        var color
        for (color in players) {
            var tr = $('<tr>');

            tr.append($('<td>').addClass('shortName').html($('<img>').attr('src', Hero.getImage(color))))

            var td = $('<td>').addClass('shortName');
            tr.append(td.html(players[color].longName))

            var td = $('<td>').css({
                border: '1px solid ' + players[color].backgroundColor
            })
            var numberOfCastlesHeld = 0,
                castleId
            for (castleId in castles) {
                if (castles[castleId].color == color) {
                    numberOfCastlesHeld++
                }
            }
            if (numberOfCastlesHeld > 0) {
                tr.append(td.html(numberOfCastlesHeld))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + players[color].backgroundColor
            })
            if (isSet(castlesConquered.winners[color])) {
                tr.append(td.html(castlesConquered.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + players[color].backgroundColor
            })
            if (isSet(castlesConquered.losers[color])) {
                tr.append(td.html(castlesConquered.losers[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + players[color].backgroundColor
            })
            if (isSet(castlesDestroyed[color])) {
                tr.append(td.html(castlesConquered[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + players[color].backgroundColor
            })
            if (isSet(soldiersCreated[color])) {
                tr.append(td.html(soldiersCreated[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + players[color].backgroundColor
            })
            if (isSet(soldiersKilled.winners[color])) {
                tr.append(td.html(soldiersKilled.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + players[color].backgroundColor
            })
            if (isSet(soldiersKilled.losers[color])) {
                tr.append(td.html(soldiersKilled.losers[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + players[color].backgroundColor
            })
            if (isSet(heroesKilled.winners[color])) {
                tr.append(td.html(heroesKilled.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + players[color].backgroundColor
            })
            if (isSet(heroesKilled.losers[color])) {
                tr.append(td.html(heroesKilled.losers[color]))
            } else {
                tr.append(td.html('0'))
            }

            table.append(tr);
        }
        statistics.append(table);

        var id = this.show(translations.statistics, statistics);
        this.ok(id)
    },
    end: function () {
        var div = $('<div>')
            .append($('<div>').html(translations.thisIsTheEnd))
        var id = this.show(translations.gameOver, div)
        this.ok(id, Gui.end)
    },
    treasury: function () {
        var myTowers = 0,
            myCastles = 0,
            myCastlesGold = 0,
            myUnits = 0,
            myUnitsGold = 0

        var i
        for (i in towers) {
            if (towers[i].color == my.color) {
                myTowers++
            }
        }

        for (i in castles) {
            if (castles[i].color == my.color) {
                myCastles++
                myCastlesGold += castles[i].income
            }
        }

        for (i in players[my.color].armies) {
            for (j in players[my.color].armies[i].soldiers) {
                myUnits++
                myUnitsGold += units[players[my.color].armies[i].soldiers[j].unitId].cost
            }
        }

        var div = $('<div>')
            .addClass('overflow')
            .append(
            $('<table>')
                .addClass('treasury')
                .append(
                $('<tr>')
                    .append($('<td>').html(myTowers).addClass('r'))
                    .append($('<td>').html(translations.towers).addClass('c'))
                    .append($('<td>').html(myTowers * 5 + ' ' + translations.gold).addClass('r'))
            )
                .append(
                $('<tr>')
                    .append($('<td>').html(myCastles).addClass('r'))
                    .append($('<td>').html(translations.castles).addClass('c'))
                    .append($('<td>').html(myCastlesGold + ' ' + translations.gold).addClass('r'))
            )
                .append(
                $('<tr>')
                    .append($('<td>'))
                    .append($('<td>'))
                    .append($('<td>').html(myTowers * 5 + myCastlesGold + ' ' + translations.gold).addClass('r'))
            )
        )
            .append($('<h3>').html(translations.upkeep))
            .append(
            $('<table>')
                .addClass('treasury')
                .append(
                $('<tr>')
                    .append($('<td>').html(myUnits).addClass('r'))
                    .append($('<td>').html(translations.units).addClass('c'))
                    .append($('<td>').html(myUnitsGold + ' ' + translations.gold).addClass('r'))
            )
        )
            .append($('<h3>').html(translations.summation))
            .append($('<div>').html(myTowers * 5 + myCastlesGold - myUnitsGold + ' ' + translations.goldPerTurn))
        var id = this.show(translations.income, div);
        this.ok(id)
    },
    income: function () {
        var myTowers = 0,
            myCastles = 0,
            myCastlesGold = 0

        for (i in towers) {
            if (towers[i].color == my.color) {
                myTowers++
            }
        }


        var table = $('<table>')
            .addClass('treasury')

        var click = function (i) {
            return function () {
                zoomer.lensSetCenter(castles[i].x * 40, castles[i].y * 40)
            }
        }

        for (i in castles) {
            if (castles[i].color == my.color) {
                myCastles++
                myCastlesGold += castles[i].income
                table.append(
                    $('<tr>')
                        .append($('<td>'))
                        .append($('<td>').html(castles[i].name))
                        .append($('<td>').html(castles[i].income + ' ' + translations.gold).addClass('r'))
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
        }
        table.append(
            $('<tr>')
                .append($('<td>').html(myCastles).addClass('r'))
                .append($('<td>').html(translations.castles).addClass('c'))
                .append($('<td>').html(myCastlesGold + ' ' + translations.gold).addClass('r'))
        ).append(
            $('<tr>')
                .append($('<td>').html(myTowers).addClass('r'))
                .append($('<td>').html(translations.towers).addClass('c'))
                .append($('<td>').html(myTowers * 5 + ' ' + translations.gold).addClass('r'))
        ).append(
            $('<tr>')
                .append($('<td>'))
                .append($('<td>'))
                .append($('<td>').html(myTowers * 5 + myCastlesGold + ' ' + translations.gold).addClass('r'))
        )


        var div = $('<div>')
            .addClass('overflow')
            .append(table)
        var id = this.show(translations.income, div);
        this.ok(id)
    },
    upkeep: function () {
        var myUnits = 0,
            myUnitsGold = 0,
            i

        var table = $('<table>')
            .addClass('treasury')

        var center = function (i) {
            return function () {
                zoomer.lensSetCenter(players[my.color].armies[i].x * 40, players[my.color].armies[i].y * 40)
            }
        }

        for (i in players[my.color].armies) {
            for (j in players[my.color].armies[i].soldiers) {
                myUnits++
                myUnitsGold += units[players[my.color].armies[i].soldiers[j].unitId].cost
                table.append(
                    $('<tr>')
                        .append($('<td>').html($('<img>').attr('src', Unit.getImage(players[my.color].armies[i].soldiers[j].unitId, my.color))))
                        .append($('<td>').html(units[players[my.color].armies[i].soldiers[j].unitId].name_lang))
                        .append($('<td>').html(units[players[my.color].armies[i].soldiers[j].unitId].cost + ' ' + translations.gold).addClass('r'))
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
                .append($('<td>').html(myUnits).addClass('r'))
                .append($('<td>').html(translations.units).addClass('c'))
                .append($('<td>').html(myUnitsGold + ' ' + translations.gold).addClass('r'))
        )

        var div = $('<div>')
            .addClass('overflow')
            .append(table)
        var id = this.show(translations.upkeep, div);
        this.ok(id)
    },
    hire: function () {
        var id = this.show(translations.hireHero, $('<div>').html(translations.doYouWantToHireNewHeroFor1000Gold))
        this.ok(id, Websocket.hire)
        this.cancel(id)
    },
    resurrection: function () {
        var id = this.show(translations.resurrectHero, $('<div>').append(translations.doYouWantToResurrectHeroFor100Gold))
        this.ok(id, Websocket.resurrection)
        this.cancel(id)
    },
    battleConfiguration: function (type) {
        var sequenceNumber = $('<div>'),
            sequenceImage = $('<div>').attr('id', 'sortable'),
            i = 0

        for (k in my.battleSequence[type]) {
            var unitId = my.battleSequence[type][k]
            if (parseInt(unitId) == shipId) {
                continue
            }
            i++
            if (isSet(units[unitId].name_lang)) {
                var name = units[unitId].name_lang
            } else {
                var name = units[unitId].name
            }
            sequenceNumber
                .append($('<div>').html(i).addClass('battleNumber'))
            sequenceImage
                .append(
                $('<div>')
                    .append($('<img>').attr({
                        src: Unit.getImage(unitId, my.color),
                        id: unitId,
                        alt: name
                    }))
                    .addClass('battleUnit')
            )
        }

        return sequenceNumber.add(sequenceImage)
    },
    battleAttack: function () {

        var div = $('<div>')
            .append($('<div>').html(translations.changeBattleAttackSequenceByMovingUnits))
            .append($('<br>'))
            .append(this.battleConfiguration('attack'))

        var id = this.show(translations.battleConfiguration, div)
        this.ok(id, Websocket.battleAttack)
        this.cancel(id)

        $("#sortable").sortable()
        $("#sortable").disableSelection()

    },
    battleDefence: function () {

        var div = $('<div>')
            .append($('<div>').html(translations.changeBattleDefenceSequenceByMovingUnits))
            .append($('<br>'))
            .append(this.battleConfiguration('defence'))

        var id = this.show(translations.battleConfiguration, div)
        this.ok(id, Websocket.battleDefence)
        this.cancel(id)

        $("#sortable").sortable()
        $("#sortable").disableSelection()

    }
}
