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
            var maxHeight = Zoom.gameHeight - 120
            if (maxHeight < parseInt($('#' + id).css('min-height'))) {
                maxHeight = parseInt($('#' + id).css('min-height'))
            }
            var maxWidth = Zoom.gameWidth - 600
            if (maxWidth < parseInt($('#' + id).css('min-width'))) {
                maxWidth = parseInt($('#' + id).css('min-width'))
            }
            $('#' + id)
                .css({
                    'max-width': maxWidth + 'px',
                    'max-height': maxHeight + 'px'
                })
//                .jScrollPane()
            var left = Zoom.gameWidth / 2 - $('#' + id).outerWidth() / 2;
            $('#' + id)
                .css({
                    left: left + 'px'
                })
        } else if ($('.message').length) {
            var maxHeight = Zoom.gameHeight - 120
            if (maxHeight < parseInt($('.message').css('min-height'))) {
                console.log(maxHeight)
                maxHeight = parseInt($('.message').css('min-height'))
            }
            var maxWidth = Zoom.gameWidth - 600
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
            var left = Zoom.gameWidth / 2 - $('.message').outerWidth() / 2;
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
        if (Me.isSelected()) {
            Me.setIsSelected(0)
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
        if (game.me.turn && Turn.number == 1 && game.players[Me.getColor()].castles[firstCastleId].currentProductionId === null) {
            Message.castle(firstCastleId);
        } else {
            var id = this.simple(translations.yourTurn, translations.thisIsYourTurnNow)
        }
    },
    castle: function (castle) {
        var time = '',
            attr,
            capital = '',
            table = $('<table>'),
            j = 0,
            td = new Array(),
            castle = castle.toArray()

        if (castle.capital) {
            capital = ' - ' + translations.capitalCity;
        }

        for (var unitId in castle.production) {
            var travelBy = '',
                unit = Units.get(unitId)
            if (unitId == castle.currentProductionId) {
                attr = {
                    type: 'radio',
                    name: 'production',
                    value: unitId,
                    checked: 'checked'
                }
                time = castle.currentProductionTurn + '/';
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
                    .append($('<img>').attr('src', Unit.getImage(unitId, Me.getColor())))
                    .addClass('img')
            )
                .append(
                $('<div>')
                    .addClass('attributes')
                    .append($('<p>').html(translations.time + ':&nbsp;' + time + castle.production[unitId].time + ' ' + translations.turn))
                    .append($('<p>').html(translations.cost + ':&nbsp;' + unit.cost + ' ' + translations.gold))
                    .append($('<p>').html(translations.moves + '&nbsp;' + unit.numberOfMoves + '&nbsp;/&nbsp;' + translations.attack + '&nbsp;' + unit.attackPoints + '&nbsp;/&nbsp;' + translations.defence + '&nbsp;' + unit.defensePoints))
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
        if (castle.currentProductionId) {
            if (Me.countCastles() > 1) {
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
                Zoom.lens.setcenter(castles[i].x, castles[i].y)
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
                .click(center(castle.id))
                .attr('id', 'center')
        )
            .append($('<h3>').append(castle.name).append(capital).addClass('name'))
            .append($('<h5>').append(translations.castleDefense + ': ' + castle.defense))
            .append($('<h5>').append(translations.income + ': ' + castle.income + ' ' + translations.gold_turn))
            .append($('<br>'))
            .append($('<fieldset>').addClass('production').append($('<label>').html(translations.production)).append(table).attr('id', castle.id))

        // relocation to

        if (castle.color == Me.getColor() && castle.relocationToCastleId && castle.currentProductionId) {
            div
                .append($('<br>'))
                .append($('<fieldset>').addClass('relocatedProduction').append($('<label>').html(translations.relocatingTo)).append(
                    $('<table>').append(
                        $('<tr>')
                            .append(
                            $('<td>').append($('<img>').attr('src', Unit.getImage(castle.currentProductionId, Me.getColor())))
                        )
                            .append(
                            $('<td>')
                                .html(castle.currentProductionTurn + ' / ' + castle.production[castle.currentProductionId].time)
                        )
                            .append(
                            $('<td>')
                                .html(castles[castle.relocationToCastleId].name)
                                .addClass('button buttonColors')
                                .click(function () {
                                    Message.castle(castle.relocationToCastleId)
                                })
                        )
                            .append(
                            $('<td>')
                                .html($('<img>').attr('src', '/img/game/center.png'))
                                .addClass('iconButton buttonColors')
                                .click(center(castle.relocationToCastleId))
                        )
                    )
                ))
        }

        // relocation from

        if (isSet(castle.relocatedProduction) && !$.isEmptyObject(castle.relocatedProduction)) {
            var relocatingFrom = $('<table>'),
                click = function (i) {
                    return function () {
                        Message.castle(i)
                    }
                }

            for (var castleIdFrom in castle.relocatedProduction) {
                var currentProductionId = castles[castleIdFrom].currentProductionId
                relocatingFrom.append(
                    $('<tr>')
                        .append(
                        $('<td>').append(
                            $('<img>').attr('src', Unit.getImage(currentProductionId, Me.getColor()))
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

        for (var soldierId in Army.selected.soldiers) {
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Unit.getImage(Army.selected.soldiers[soldierId].unitId, Army.selected.color),
                            'id': 'unit' + soldierId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + Army.selected.soldiers[soldierId].movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'soldierId',
                        value: soldierId
                    })))
            );
        }

        for (var heroId in Army.selected.heroes) {
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Hero.getImage(Army.selected.color),
                            'id': 'hero' + heroId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + Army.selected.heroes[heroId].movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'heroId',
                        value: heroId
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
                zoom.lens.setcenter(castles[i].x * 40, castles[i].y * 40)
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
                                .append($('<td>').html(game.units[Army.selected.soldiers[i].unitId].numberOfMoves).addClass('value'))
                        )
                            .append(
                            $('<tr>')
                                .append($('<td>').html(translations.attackPoints + ': '))
                                .append(
                                $('<td>')
                                    .append($('<div>').html(game.units[Army.selected.soldiers[i].unitId].attackPoints))
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
                                    .append($('<div>').html(game.units[Army.selected.soldiers[i].unitId].defensePoints))
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
                                .append($('<td>').html(game.units[Army.selected.soldiers[i].unitId].f).addClass('value'))
                        )
                            .append(
                            $('<tr>')
                                .append($('<td>').html(translations.movementCostThroughTheSwamp + ': '))
                                .append($('<p>').html(game.units[Army.selected.soldiers[i].unitId].s).addClass('value')))
                            .append(
                            $('<tr>')
                                .append($('<td>').html(translations.movementCostThroughTheHills + ': '))
                                .append($('<p>').html(game.units[Army.selected.soldiers[i].unitId].m).addClass('value'))
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
        var killed = new Array();

        var attack = $('<div>').addClass('battle attack');

        for (var soldierId in r.battle.attack.soldiers) {
            if (r.battle.attack.soldiers[soldierId]) {
                killed[r.battle.attack.soldiers[soldierId]] = {
                    'soldierId': soldierId
                };
            }
            attack.append(
                $('<div>')
                    .attr('id', 'unit' + soldierId)
                    .css('background', 'url(' + Unit.getImage(game.players[r.color].armies[r.army.armyId].soldiers[soldierId].unitId, r.color) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }
        for (var heroId in r.battle.attack.heroes) {
            if (r.battle.attack.heroes[heroId]) {
                killed[r.battle.attack.heroes[heroId]] = {
                    'heroId': heroId
                };
            }
            attack.append(
                $('<div>')
                    .attr('id', 'hero' + heroId)
                    .css('background', 'url(' + Hero.getImage(r.color) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }

        var attackLayout = $('<div>')
            .append(attack)
            .append($('<div>').html(game.players[r.color].longName + ' (' + translations.attack + ')'))

        var defense = $('<div>').addClass('battle defense');
        var defenseLayout = $('<div>')

        for (var color in r.battle.defenders) {
            for (var armyId in r.battle.defenders[color]) {
                for (var soldierId in r.battle.defenders[color][armyId].soldiers) {
                    if (r.battle.defenders[color][armyId].soldiers[soldierId]) {
                        killed[r.battle.defenders[color][armyId].soldiers[soldierId]] = {
                            'soldierId': soldierId
                        };
                    }
                    if (color == 'neutral') {
                        var unitId = game.firstUnitId
                    } else {
                        var unitId = game.players[color].armies[armyId].soldiers[soldierId].unitId
                    }
                    defense.append(
                        $('<div>')
                            .attr('id', 'unit' + soldierId)
                            .css('background', 'url(' + Unit.getImage(unitId, color) + ') no-repeat')
                            .addClass('battleUnit')
                    );
                }
                for (var heroId in r.battle.defenders[color][armyId].heroes) {
                    if (r.battle.defenders[color][armyId].heroes[heroId]) {
                        killed[r.battle.defenders[color][armyId].heroes[heroId]] = {
                            'heroId': heroId
                        };
                    }
                    defense.append(
                        $('<div>')
                            .attr('id', 'hero' + heroId)
                            .css('background', 'url(' + Hero.getImage(color) + ') no-repeat')
                            .addClass('battleUnit')
                    );
                }
            }

            defenseLayout.append($('<div>').html(game.players[color].longName + ' (' + translations.defence + ')'))

            if (r.battle.castleId && isSet(game.players[color].castles[r.battle.castleId])) {
                defenseLayout.append(
                    $('<div>')
                        .addClass('castle')
                        .css({
                            position: 'static',
                            background: 'url(/img/game/castles/' + color + '.png) center center no-repeat',
                            margin: '0 auto'
                        })
                )
            }

            if (r.battle.towerId && isSet(game.players[color].towers[r.battle.towerId])) {
                defenseLayout.append(
                    $('<div>')
                        .addClass('tower')
                        .css({
                            position: 'static',
                            background: 'url(/img/game/towers/' + color + '.png) center center no-repeat',
                            margin: '0 auto'
                        })
                )
            }

            defenseLayout.append(defense)
        }

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
        if (!game.players[r.color].computer) {
            this.ok(id)// add Move.end(r)
        } else {
            this.ok(id)
        }

        $('.go').css('display', 'none')

        if (killed) {
            if (game.players[r.color].computer) {
                Message.kill(killed, r);
            } else {
                setTimeout(function () {
                    Message.kill(killed, r);
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
            if (!game.players[r.color].computer) {
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
            if (!game.players[r.color].computer) {
                setTimeout(function () {
                    Sound.play('error');
                }, 500)
            }
            $('#unit' + b[i].soldierId + ' .killed').fadeIn(1000, function () {
                if (r.color == Me.getColor()) {
                    for (k in game.players[Me.getColor()].armies[r.army.armyId].soldiers) {
                        if (game.players[Me.getColor()].armies[r.army.armyId].soldiers[k].soldierId == b[i].soldierId) {
                            costIncrement(-game.units[game.players[Me.getColor()].armies[r.army.armyId].soldiers[k].unitId].cost)
                        }
                    }
                }

                for (var color in r.defenders) {
                    if (color == Me.getColor()) {
                        for (j in r.defenders[color]) {
                            for (k in game.players[color].armies[j].soldiers) {
                                if (game.players[color].armies[j].soldiers[k].soldierId == b[i].soldierId) {
                                    costIncrement(-game.units[game.players[color].armies[j].soldiers[k].unitId].cost)
                                }
                            }
                        }
                        break;
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
            if (!game.players[r.color].computer) {
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
        for (color in game.players) {
            var tr = $('<tr>');

            tr.append($('<td>').addClass('shortName').html($('<img>').attr('src', Hero.getImage(color))))

            var td = $('<td>').addClass('shortName');
            tr.append(td.html(game.players[color].longName))

            var td = $('<td>').css({
                border: '1px solid ' + game.players[color].backgroundColor
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
                border: '1px solid ' + game.players[color].backgroundColor
            })
            if (isSet(castlesConquered.winners[color])) {
                tr.append(td.html(castlesConquered.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + game.players[color].backgroundColor
            })
            if (isSet(castlesConquered.losers[color])) {
                tr.append(td.html(castlesConquered.losers[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + game.players[color].backgroundColor
            })
            if (isSet(castlesDestroyed[color])) {
                tr.append(td.html(castlesConquered[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + game.players[color].backgroundColor
            })
            if (isSet(soldiersCreated[color])) {
                tr.append(td.html(soldiersCreated[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + game.players[color].backgroundColor
            })
            if (isSet(soldiersKilled.winners[color])) {
                tr.append(td.html(soldiersKilled.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + game.players[color].backgroundColor
            })
            if (isSet(soldiersKilled.losers[color])) {
                tr.append(td.html(soldiersKilled.losers[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + game.players[color].backgroundColor
            })
            if (isSet(heroesKilled.winners[color])) {
                tr.append(td.html(heroesKilled.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + game.players[color].backgroundColor
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
            if (towers[i].color == Me.getColor()) {
                myTowers++
            }
        }

        for (i in castles) {
            if (castles[i].color == Me.getColor()) {
                myCastles++
                myCastlesGold += castles[i].income
            }
        }

        for (i in game.players[Me.getColor()].armies) {
            for (j in game.players[Me.getColor()].armies[i].soldiers) {
                myUnits++
                myUnitsGold += game.units[game.players[Me.getColor()].armies[i].soldiers[j].unitId].cost
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
            if (towers[i].color == Me.getColor()) {
                myTowers++
            }
        }


        var table = $('<table>')
            .addClass('treasury')

        var click = function (i) {
            return function () {
                zoom.lens.setcenter(castles[i].x * 40, castles[i].y * 40)
            }
        }

        for (i in castles) {
            if (castles[i].color == Me.getColor()) {
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
                zoom.lens.setcenter(game.players[Me.getColor()].armies[i].x * 40, game.players[Me.getColor()].armies[i].y * 40)
            }
        }

        for (i in game.players[Me.getColor()].armies) {
            for (j in game.players[Me.getColor()].armies[i].soldiers) {
                myUnits++
                myUnitsGold += game.units[game.players[Me.getColor()].armies[i].soldiers[j].unitId].cost
                table.append(
                    $('<tr>')
                        .append($('<td>').html($('<img>').attr('src', Unit.getImage(game.players[Me.getColor()].armies[i].soldiers[j].unitId, Me.getColor()))))
                        .append($('<td>').html(game.units[game.players[Me.getColor()].armies[i].soldiers[j].unitId].name_lang))
                        .append($('<td>').html(game.units[game.players[Me.getColor()].armies[i].soldiers[j].unitId].cost + ' ' + translations.gold).addClass('r'))
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

        for (k in game.me.battleSequence[type]) {
            var unitId = game.me.battleSequence[type][k]
            if (parseInt(unitId) == shipId) {
                continue
            }
            i++
            if (isSet(game.units[unitId].name_lang)) {
                var name = game.units[unitId].name_lang
            } else {
                var name = game.units[unitId].name
            }
            sequenceNumber
                .append($('<div>').html(i).addClass('battleNumber'))
            sequenceImage
                .append(
                $('<div>')
                    .append($('<img>').attr({
                        src: Unit.getImage(unitId, Me.getColor()),
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
