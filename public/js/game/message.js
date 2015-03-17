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
        if (Turn.isMy() && Turn.getNumber() == 1 && !Me.getCastle(Me.getFirsCastleId()).getProductionId()) {
            Message.castle(Me.getCastle(Me.getFirsCastleId()))
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
            td = new Array()

        if (castle.getCapital()) {
            capital = ' - ' + translations.capitalCity;
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
                    .append($('<p>').html(translations.time + ':&nbsp;' + time + castle.getProduction()[unitId].time + ' ' + translations.turn))
                    .append($('<p>').html(translations.cost + ':&nbsp;' + unit.cost + ' ' + translations.gold))
                    .append($('<p>').html(translations.moves + '&nbsp;' + unit.moves + '&nbsp;/&nbsp;' + translations.attack + '&nbsp;' + unit.a + '&nbsp;/&nbsp;' + translations.defence + '&nbsp;' + unit.d))
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
        if (castle.getProductionId()) {
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
                                castle.handle(1, 0)
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
                                castle.handle(0, 1)
                            }
                        })
                )
            )
        );

        var center = function (i) {
            return function () {
                Zoom.lens.setcenter(Me.getCastle(i).getX(), Me.getCastle(i).getY())
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
                .click(center(castle.getCastleId()))
                .attr('id', 'center')
        )
            .append($('<h3>').append(castle.getName()).append(capital).addClass('name'))
            .append($('<h5>').append(translations.castleDefense + ': ' + castle.getDefense()))
            .append($('<h5>').append(translations.income + ': ' + castle.getIncome() + ' ' + translations.gold_turn))
            .append($('<br>'))
            .append($('<fieldset>').addClass('production').append($('<label>').html(translations.production)).append(table).attr('id', castle.getCastleId()))

        // relocation to

        if (castle.getRelocationCastleId() && Me.getCastles().has(castle.getRelocationCastleId())) {
            div
                .append($('<br>'))
                .append($('<fieldset>').addClass('relocatedProduction').append($('<label>').html(translations.relocatingTo)).append(
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
            var relocatingFrom = $('<table>'),
                click = function (i) {
                    return function () {
                        Message.castle(Me.getCastle(i))
                    }
                }

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
            div
                .append($('<br>'))
                .append($('<fieldset>').addClass('relocatedProduction').append($('<label>').html(translations.relocatingFrom)).append(relocatingFrom))
        }

        var id = this.show('', div);
        this.ok(id, castle.handle);
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

            if (Me.getCastles().count() > 1) {
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
        var numberOfUnits = 0,
            walk = Me.getSelectedArmy().getWalkingSoldiers(),
            swim = Me.getSelectedArmy().getSwimmingSoldiers(),
            fly = Me.getSelectedArmy().getFlyingSoldiers(),
            heroes = Me.getSelectedArmy().getHeroes()

        for (var soldierId in walk) {
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Unit.getImage(walk[soldierId].unitId, Me.getColor()),
                            'id': 'unit' + soldierId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + walk[soldierId].movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'soldierId',
                        value: soldierId
                    })))
            );
        }
        for (var soldierId in swim) {
            var soldier = swim[soldierId]
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Unit.getImage(soldier.unitId, Me.getColor()),
                            'id': 'unit' + soldierId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + soldier.movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'soldierId',
                        value: soldierId
                    })))
            );
        }
        for (var soldierId in fly) {
            var soldier = fly[soldierId]
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Unit.getImage(soldier.unitId, Me.getColor()),
                            'id': 'unit' + soldierId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + soldier.movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'soldierId',
                        value: soldierId
                    })))
            );
        }
        for (var heroId in heroes) {
            numberOfUnits++;
            div.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Hero.getImage(Me.getColor()),
                            'id': 'hero' + heroId
                        })
                    ))
                    .append($('<span>').html(translations.movesLeft + ': ' + heroes[heroId].movesLeft + ' '))
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
            div.append(this.statusContent(numberOfUnits, army.getWalkingSoldier(i), color, attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
        }
        for (var i in army.getSwimmingSoldiers()) {
            numberOfUnits++
            div.append(this.statusContent(numberOfUnits, army.getSwimmingSoldier(i), color, attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
        }
        for (var i in army.getFlyingSoldiers()) {
            numberOfUnits++
            div.append(this.statusContent(numberOfUnits, army.getFlyingSoldier(i), color, attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus));
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

        var id = this.show(translations.status, div);
        this.ok(id)
    },
    battle: function (r, ii) {
        var killed = new Array();

        var attack = $('<div>').addClass('battle attack');

        for (var soldierId in r.battle.attack.walk) {
            if (r.battle.attack.walk[soldierId]) {
                killed[r.battle.attack.walk[soldierId]] = {
                    'soldierId': soldierId
                };
            }
            attack.append(
                $('<div>')
                    .attr('id', 'unit' + soldierId)
                    .css('background', 'url(' + Unit.getImage(Players.get(r.color).getArmies().get(r.army.id).getWalkingSoldier(soldierId).unitId, r.color) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }
        for (var soldierId in r.battle.attack.swim) {
            if (r.battle.attack.swim[soldierId]) {
                killed[r.battle.attack.swim[soldierId]] = {
                    'soldierId': soldierId
                };
            }
            attack.append(
                $('<div>')
                    .attr('id', 'unit' + soldierId)
                    .css('background', 'url(' + Unit.getImage(Players.get(r.color).getArmies().get(r.army.id).getSwimmingSoldier(soldierId).unitId, r.color) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }
        for (var soldierId in r.battle.attack.fly) {
            if (r.battle.attack.fly[soldierId]) {
                killed[r.battle.attack.fly[soldierId]] = {
                    'soldierId': soldierId
                };
            }
            attack.append(
                $('<div>')
                    .attr('id', 'unit' + soldierId)
                    .css('background', 'url(' + Unit.getImage(Players.get(r.color).getArmies().get(r.army.id).getFlyingSoldier(soldierId).unitId, r.color) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }
        for (var heroId in r.battle.attack.hero) {
            if (r.battle.attack.hero[heroId]) {
                killed[r.battle.attack.hero[heroId]] = {
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
            .append($('<div>').html(Players.get(r.color).getLongName() + ' (' + translations.attack + ')'))

        var defense = $('<div>').addClass('battle defense');
        var defenseLayout = $('<div>')

        for (var color in r.battle.defenders) {
            for (var armyId in r.battle.defenders[color]) {
                for (var soldierId in r.battle.defenders[color][armyId].walk) {
                    if (r.battle.defenders[color][armyId].walk[soldierId]) {
                        killed[r.battle.defenders[color][armyId].walk[soldierId]] = {
                            'soldierId': soldierId
                        };
                    }
                    if (color == 'neutral') {
                        var unitId = Game.getFirstUnitId()
                    } else {
                        var unitId = Players.get(color).getArmies().get(armyId).getWalkingSoldier(soldierId).unitId
                    }
                    defense.append(
                        $('<div>')
                            .attr('id', 'unit' + soldierId)
                            .css('background', 'url(' + Unit.getImage(unitId, color) + ') no-repeat')
                            .addClass('battleUnit')
                    );
                }
                for (var soldierId in r.battle.defenders[color][armyId].swim) {
                    if (r.battle.defenders[color][armyId].swim[soldierId]) {
                        killed[r.battle.defenders[color][armyId].swim[soldierId]] = {
                            'soldierId': soldierId
                        };
                    }
                    var unitId = Players.get(color).getArmies().get(armyId).getSwimmingSoldier(soldierId).unitId
                    defense.append(
                        $('<div>')
                            .attr('id', 'unit' + soldierId)
                            .css('background', 'url(' + Unit.getImage(unitId, color) + ') no-repeat')
                            .addClass('battleUnit')
                    );
                }
                for (var soldierId in r.battle.defenders[color][armyId].fly) {
                    if (r.battle.defenders[color][armyId].fly[soldierId]) {
                        killed[r.battle.defenders[color][armyId].fly[soldierId]] = {
                            'soldierId': soldierId
                        };
                    }
                    var unitId = Players.get(color).getArmies().get(armyId).getFlyingSoldier(soldierId).unitId
                    defense.append(
                        $('<div>')
                            .attr('id', 'unit' + soldierId)
                            .css('background', 'url(' + Unit.getImage(unitId, color) + ') no-repeat')
                            .addClass('battleUnit')
                    );
                }
                for (var heroId in r.battle.defenders[color][armyId].hero) {
                    if (r.battle.defenders[color][armyId].hero[heroId]) {
                        killed[r.battle.defenders[color][armyId].hero[heroId]] = {
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

            defenseLayout.append($('<div>').html(Players.get(color).getLongName() + ' (' + translations.defence + ')'))

            //if (r.battle.castleId && isSet(Players.get(color).getCastles()[r.battle.castleId])) {
            if (r.battle.castleId) {
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

            //if (r.battle.towerId && isSet(game.players[color].towers[r.battle.towerId])) {
            if (r.battle.towerId) {
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
        if (!Players.get(r.color).isComputer()) {
            this.ok(id)// add Move.end(r)
        } else {
            this.ok(id)
        }

        $('.go').css('display', 'none')

        if (killed) {
            if (Players.get(r.color).isComputer()) {
                Message.kill(killed, r, ii);
            } else {
                setTimeout(function () {
                    Message.kill(killed, r, ii);
                }, 2500);
            }
        }
    },
    kill: function (b, r, ii) {
        for (var i in b) {
            break
        }

        if (notSet(b[i])) {
            if (!Players.get(r.color).isComputer()) {
                $('.go').fadeIn(100)
            }
            Move.end(r, ii)
            return
        }

        if (isSet(b[i].soldierId)) {
            var unitElement = $('#unit' + b[i].soldierId)
            if (!unitElement.length) {
                Move.end(r, ii)
            }

            unitElement.append($('<div>').addClass('killed'));
            if (!Players.get(r.color).isComputer()) {
                setTimeout(function () {
                    Sound.play('error');
                }, 500)
            }
            $('#unit' + b[i].soldierId + ' .killed').fadeIn(1000, function () {
                if (Me.colorEquals(r.color)) {
                    for (var k in Me.getArmy(r.army.id).getWalkingSoldiers()) {
                        if (Me.getArmy(r.army.id).getWalkingSoldier(k).soldierId == b[i].soldierId) {
                            Me.costIncrement(-Units[Me.getArmy(r.army.id).getWalkingSoldier(k).unitId].cost)
                        }
                    }
                    for (var k in Me.getArmy(r.army.id).getSwimmingSoldiers()) {
                        if (Me.getArmy(r.army.id).getSwimmingSoldier(k).soldierId == b[i].soldierId) {
                            Me.costIncrement(-Units[Me.getArmy(r.army.id).getSwimmingSoldier(k).unitId].cost)
                        }
                    }
                    for (var k in Me.getArmy(r.army.id).getFlyingSoldiers()) {
                        if (Me.getArmy(r.army.id).getFlyingSoldier(k).soldierId == b[i].soldierId) {
                            Me.costIncrement(-Units[Me.getArmy(r.army.id).getFlyingSoldier(k).unitId].cost)
                        }
                    }
                }

                for (var color in r.defenders) {
                    if (Me.colorEquals(color)) {
                        for (var armyId in r.defenders[color]) {
                            for (var soldierId in Me.getArmy(armyId).getWalkingSoldiers()) {
                                if (Me.getArmy().getWalkingSoldier(soldierId).soldierId == b[i].soldierId) {
                                    Me.costIncrement(-Units[Me.getArmy(armyId).getWalkingSoldier(soldierId).unitId].cost)
                                }
                            }
                            for (var soldierId in Me.getArmy(armyId).getSwimmingSoldiers()) {
                                if (Me.getArmy().getSwimmingSoldier(soldierId).soldierId == b[i].soldierId) {
                                    Me.costIncrement(-Units[Me.getArmy(armyId).getSwimmingSoldier(soldierId).unitId].cost)
                                }
                            }
                            for (var soldierId in Me.getArmy(armyId).getFlyingSoldiers()) {
                                if (Me.getArmy().getFlyingSoldier(soldierId).soldierId == b[i].soldierId) {
                                    Me.costIncrement(-Units[Me.getArmy(armyId).getFlyingSoldier(soldierId).unitId].cost)
                                }
                            }
                        }
                        break;
                    }
                }
                delete b[i];
                Message.kill(b, r, ii);
            });
        } else if (isSet(b[i].heroId)) {
            var heroElement = $('#hero' + b[i].heroId)
            if (!heroElement.length) {
                Move.end(r, ii)
            }

            heroElement.append($('<div>').addClass('killed'));
            if (!Players.get(r.color).isComputer()) {
                setTimeout(function () {
                    Sound.play('error');
                }, 500)
            }
            $('#hero' + b[i].heroId + ' .killed').fadeIn(1000, function () {
                delete b[i];
                Message.kill(b, r, ii);
            });
        }
    },
    raze: function () {
        if (!Me.getSelectedArmyId()) {
            return;
        }
        var id = this.simple(translations.destroyCastle, translations.areYouSure)
        this.ok(id, Websocket.raze);
        this.cancel(id)
    },
    build: function () {
        if (!Me.getSelectedArmyId()) {
            return;
        }

        var army = Me.getArmy(Me.getSelectedArmyId())
        var castle = Me.getCastle(Fields.get(army.getX(), army.getY()).getCastleId())

        var costBuildDefense = 0;
        for (i = 1; i <= castle.getDefense(); i++) {
            costBuildDefense += i * 100;
        }
        var newDefense = castle.getDefense() + 1;

        var div = $('<div>')
            .append($('<div>').html(translations.currentDefense + ': ' + castle.getDefense()))
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
        for (var color in Players.toArray()) {
            var tr = $('<tr>'),
                player = Players.get(color),
                numberOfCastlesHeld = player.getCastles().count(),
                backgroundColor = player.getBackgroundColor()

            tr.append($('<td>').addClass('shortName').html($('<img>').attr('src', Hero.getImage(color))))

            var td = $('<td>').addClass('shortName');
            tr.append(td.html(player.getLongName()))

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })

            if (numberOfCastlesHeld > 0) {
                tr.append(td.html(numberOfCastlesHeld))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(castlesConquered.winners[color])) {
                tr.append(td.html(castlesConquered.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(castlesConquered.losers[color])) {
                tr.append(td.html(castlesConquered.losers[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(castlesDestroyed[color])) {
                tr.append(td.html(castlesConquered[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(soldiersCreated[color])) {
                tr.append(td.html(soldiersCreated[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(soldiersKilled.winners[color])) {
                tr.append(td.html(soldiersKilled.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(soldiersKilled.losers[color])) {
                tr.append(td.html(soldiersKilled.losers[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
            })
            if (isSet(heroesKilled.winners[color])) {
                tr.append(td.html(heroesKilled.winners[color]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                border: '1px solid ' + backgroundColor
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
            for (var j in army.getSwimmingSoldiers()) {
                myUnitsOutcome += Units.get(army.getSwimmingSoldier(j).unitId).cost
                myUnits++
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
                    .append($('<td>').html(myCastlesIncome + ' ' + translations.gold).addClass('r'))
            )
                .append(
                $('<tr>')
                    .append($('<td>'))
                    .append($('<td>'))
                    .append($('<td>').html(myTowers * 5 + myCastlesIncome + ' ' + translations.gold).addClass('r'))
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
                    .append($('<td>').html(myUnitsOutcome + ' ' + translations.gold).addClass('r'))
            )
        )
            .append($('<h3>').html(translations.summation))
            .append($('<div>').html(myTowers * 5 + myCastlesIncome - myUnitsOutcome + ' ' + translations.goldPerTurn))
        var id = this.show(translations.income, div);
        this.ok(id)
    },
    income: function () {
        var myTowers = Me.getTowers().count(),
            myCastles = 0,
            myCastlesIncome = 0

        var table = $('<table>')
            .addClass('treasury')

        var click = function (i) {
            return function () {
                Zoom.lens.setcenter(Me.getCastle(i).getX(), Me.getCastle(i).getY())
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
                .append($('<td>'))
                .append($('<td>'))
                .append($('<td>').html(myTowers * 5 + myCastlesIncome + ' ' + translations.gold).addClass('r'))
        )


        var div = $('<div>')
            .addClass('overflow')
            .append(table)
        var id = this.show(translations.income, div);
        this.ok(id)
    },
    upkeep: function () {
        var myUnits = 0,
            myUnitsGold = 0

        var table = $('<table>')
            .addClass('treasury')

        var center = function (i) {
            return function () {
                Zoom.lens.setcenter(Me.getArmy(i).getX(), Me.getArmy(i).getY())
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

        for (k in Me.getBattleSequence(type)) {
            var unitId = Me.getBattleSequence(type)[k],
                unit = Units.get(unitId)
            if (unit.canFly) {
                continue
            }
            if (unit.canSwim) {
                continue
            }
            i++
            if (isSet(unit.name_lang)) {
                var name = unit.name_lang
            } else {
                var name = unit.name
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
            .append(this.battleConfiguration('defense'))

        var id = this.show(translations.battleConfiguration, div)
        this.ok(id, Websocket.battleDefence)
        this.cancel(id)

        $("#sortable").sortable()
        $("#sortable").disableSelection()

    },
    statusContent: function (numberOfUnits, soldier, color, attackFlyBonus, attackHeroBonus, defenseFlyBonus, defenseHeroBonus, defenseTowerBonus, defenseCastleBonus) {
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
}
