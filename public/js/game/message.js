var Message = {
    element: function () {
        return $('#goldBox');
    },
    remove: function () {
        if (typeof $('.message') != 'undefined') {
            $('.message').remove();
        }
    },
    show: function (txt) {
        this.remove();
        this.element().after(
            $('<div>')
                .addClass('message center')
                .append(txt)
        );
        var left = documentWidth / 2 - $('.message').outerWidth() / 2;
        var maxHeight = documentHeight - 120;
        var maxWidth = documentWidth - 500;
        $('.message').css({
            'max-width': +'px',
            'max-height': maxHeight + 'px',
            'left': left + 'px',
            'display': 'block'
        })
    },
    ok: function (func) {
        $('.message').append(
            $('<div>')
                .addClass('button buttonColors go')
                .html('Ok')
                .click(function () {
                    if (typeof func != 'undefined') {
                        func();
                    } else {
                        Message.remove();
                    }
                })
        );
//        $('<body>').keypress(function (event) {
//            console.log(event);
//            var key = event.keyCode || event.charCode;
//            if (key == 13) {
//                if (typeof func != 'undefined') {
//                    func();
//                } else {
//                    Message.remove();
//                }
//            }
//        });
    },
    cancel: function (func) {
        $('.message').append(
            $('<div>')
                .addClass('button buttonColors cancel')
                .html('Cancel')
                .click(function () {
                    if (typeof func != 'undefined') {
                        func();
                    } else {
                        Message.remove();
                    }
                })
        )
    },
    surrender: function () {
        this.show($('<div>').html('Surrender. Are you sure?'));
        this.ok(Websocket.surrender);
        this.cancel();
    },
//    lost: function (color) {
////        $('.nr.' + color).html('<img src="/img/game/skull_and_crossbones.png" />');
//        var msg;
//
//        if (color == my.color) {
//            msg = '<br/>GAME OVER<br/><br/>You lose!';
//        } else {
//            msg = color.charAt(0).toUpperCase() + color.slice(1) + ' no longer fights!';
//        }
//
//        this.show($('<div>').html(msg));
//        this.ok();
//    },
    showArtifacts: function () {
        Message.remove();

        var htmlChest = $('<div>').attr('id', 'chest');
        for (i in players[my.color].chest) {
            htmlChest.append(
                $('<div>')
                    .attr('id', players[my.color].chest[i].artifactId)
                    .html(artifacts[players[my.color].chest[i].artifactId].name + ' ' + players[my.color].chest[i].quantity)
                    .click(function () {
                        Websocket.inventoryAdd(Army.selected.heroes[0].heroId, $(this).attr('id'));
                    })
                    .mousemove(function (e) {
                        $('.zoomWindow #des' + $(this).attr('id')).remove();
                        $('.zoomWindow').append(
                            $('<div>')
                                .attr('id', 'des' + $(this).attr('id'))
                                .addClass('artifactDescription')
                                .css({
                                    top: e.pageY + 'px',
                                    left: e.pageX + 'px'

                                })
                                .append(
                                    '<h3>' + artifacts[$(this).attr('id')].name + '</h3><div>' + artifacts[$(this).attr('id')].description + '</div>'
                                )
                        );
                    })
                    .mouseleave(function () {
                        $('.zoomWindow #des' + $(this).attr('id')).remove();
                    })
            );
        }

        var htmlInventory = $('<div>').attr('id', 'inventory');

        for (i in Army.selected.heroes[0].artifacts) {
            htmlInventory.append(
                $('<div>')
                    .attr('id', Army.selected.heroes[0].artifacts[i].artifactId)
                    .html(artifacts[Army.selected.heroes[0].artifacts[i].artifactId].name)
                    .click(function () {
                        Websocket.inventoryDel(Army.selected.heroes[0].heroId, $(this).attr('id'));
                    })
                    .mousemove(function (e) {
                        $('.zoomWindow #des' + $(this).attr('id')).remove();
                        $('.zoomWindow').append(
                            $('<div>')
                                .attr('id', 'des' + $(this).attr('id'))
                                .addClass('artifactDescription')
                                .css({
                                    top: e.pageY + 'px',
                                    left: e.pageX + 'px'

                                })
                        );
                    })
                    .mouseleave(function () {
                        $('.zoomWindow #des' + $(this).attr('id')).remove();
                    })
            );
        }

        this.element().after(
            $('<div>')
                .addClass('message')
                .addClass('center')
                .append($('<h3>').html('Chest'))
                .append(htmlChest)
                .append($('<h3>').html('Inventory'))
                .append(htmlInventory)
                .append($('<div>')
                    .addClass('button buttonColors go')
                    .html('Ok')
                    .click(function () {
                        Message.remove();
                    })
                )
                .css({
                    'left': this.left + 'px'
                })
        );

    },
    turn: function () {
        this.remove();
        if (my.turn && Turn.number == 1 && notSet(castles[firstCastleId].currentProduction)) {
            Message.castle(firstCastleId);
        } else {
            this.show($('<div>').html('Your turn.'));
            this.ok();
        }
    },
    castle: function (castleId) {
        if (lock) {
            return;
        }
        if (!my.turn) {
            return;
        }
        if (Army.selected) {
            return;
        }

        if (typeof castles[castleId] == 'undefined') {
            return;
        }

        var time = '';
        var attr;
        var capital = '';

        if (castles[castleId].capital) {
            capital = ' - Capital city';
        }
        var table = $('<table>');
        var j = 0;
        var td = new Array();

        for (unitId in castles[castleId].production) {
            var img = units[unitId].name.replace(' ', '_').toLowerCase();
            var travelBy = '';
            if (unitId == castles[castleId].currentProduction) {
                attr = {
                    type: 'radio',
                    name: 'production',
                    value: units[unitId].name,
                    checked: 'checked'
                }
                time = castles[castleId].currentProductionTurn + '/';
            } else {
                attr = {
                    type: 'radio',
                    name: 'production',
                    value: units[unitId].name
                }
                time = '';
            }

            if (units[unitId].canFly) {
                travelBy = 'ground / air';
            } else if (units[unitId].canSwim) {
                travelBy = 'water';
            } else {
                travelBy = 'ground';
            }
            if (units[unitId].name_lang) {
                var name = units[unitId].name_lang;
            } else {
                var name = units[unitId].name;
            }
            td[j] = $('<td>')
                .addClass('unit')
                .append(
                    $('<p>')
                        .append($('<input>').attr(attr))
                        .append(' ' + name + ' (' + travelBy + ')')
                )
                .append($('<div>').append($('<img>').attr('src', Unit.getImageByName(img, my.color))))
                .append(
                    $('<div>')
                        .addClass('attributes')
                        .append($('<p>').html('Time:&nbsp;' + time + castles[castleId].production[unitId].time + 't'))
                        .append($('<p>').html('Cost:&nbsp;' + castles[castleId].production[unitId].cost + 'g'))
                        .append($('<p>').html('M ' + units[unitId].numberOfMoves + ' . A ' + units[unitId].attackPoints + ' . D ' + units[unitId].defensePoints))
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
        table.append(
            $('<tr>')
                .append(
                    $('<td>')
                        .addClass('unit')
                        .append(
                            $('<input>').attr({
                                type: 'radio',
                                name: 'production',
                                value: 'stop'
                            })
                        )
                        .append(' Stop production')
                )
        );
        var resurrectionElement;
        var resurrection = true;
        for (armyId in players[my.color].armies) {
            for (j in players[my.color].armies[armyId].heroes) {
                resurrection = false;
            }
        }
        if (resurrection) {
            var resurrectionElement = $('<fieldset>')
                .append($('<label>').html('Hero resurrection'))
                .append(
                    $('<div>')
                        .append(
                            $('<input>').attr({
                                type: 'checkbox',
                                name: 'resurrection',
                                value: castleId
                            })
                        )
                        .append(' cost 100g')
                )
        }
        var buttonBuildDefense;
        var costBuildDefense = 0;
        for (i = 1; i <= castles[castleId].defense; i++) {
            costBuildDefense += i * 100;
        }

        var div = $('<div>')
            .append($('<h3>').append(castles[castleId].name).append(capital))
            .append($('<h5>').append('Castle defense: ' + castles[castleId].defense))
            .append($('<h5>').append('Income: ' + castles[castleId].income + ' gold/turn'))
            .append(
                $('<fieldset>')
                    .append($('<label>').html('Build castle defense'))
                    .append(
                        $('<div>')
                            .append(
                                $('<input>').attr({
                                    type: 'checkbox',
                                    name: 'defense',
                                    value: castleId
                                })
                            )
                            .append(' cost ' + costBuildDefense + 'g')
                    )
            )
            .append($('<br>'))
            .append($('<fieldset>').addClass('production').append($('<label>').html('Production')).append(table))
            .append($('<br>'))
            .append(resurrectionElement);

        Message.show(div.html());
        this.ok(Castle.handle);
        this.cancel();
    },
    nextTurn: function () {
        this.show($('<div>').html('Next turn. Are you sure?'));
        this.ok(Websocket.nextTurn);
        this.cancel();
    },
//    win: function (color) {
//        setLock();
//
//        var msg;
//
//        if (color == my.color) {
//            msg = '<br/>GAME OVER<br/><br/>You won!';
//
//        } else {
//            msg = '<br/>GAME OVER<br/><br/>' + color.charAt(0).toUpperCase() + color.slice(1) + ' won!';
//        }
//
//        this.show($('<div>').html(msg));
//        this.ok(Message.remove);
//    },
    simple: function (message) {
        this.show($('<div>').html(message));
        this.ok(Message.remove);
    },
    disband: function () {
        if (typeof Army.selected == 'undefined') {
            return;
        }

        if (!my.turn) {
            return;
        }

        if (!Army.selected) {
            return;
        }

        this.show($('<div>').html('Are you sure?'));
        this.ok(Websocket.disband);
        this.cancel();
    },
    split: function (a) {
        if (typeof Army.selected == 'undefined') {
            return;
        }
        Message.remove();
        var army = $('<div>').addClass('split').css('max-height', documentHeight - 200 + 'px');
        var numberOfUnits = 0;

        for (i in Army.selected.soldiers) {
            var img = units[Army.selected.soldiers[i].unitId].name.replace(' ', '_').toLowerCase();
            numberOfUnits++;
            army.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Unit.getImageByName(img, Army.selected.color),
                            'id': 'unit' + Army.selected.soldiers[i].soldierId
                        })
                    ))
                    .append($('<span>').html(' Moves left: ' + Army.selected.soldiers[i].movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'soldierId',
                        value: Army.selected.soldiers[i].soldierId
                    })))
            );
        }
        for (i in Army.selected.heroes) {
            numberOfUnits++;
            army.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Hero.getImage(Army.selected.color),
                            'id': 'hero' + Army.selected.heroes[i].heroId
                        })
                    ))
                    .append($('<span>').html(' Moves left: ' + Army.selected.heroes[i].movesLeft + ' '))
                    .append($('<div>').addClass('right').html($('<input>').attr({
                        type: 'checkbox',
                        name: 'heroId',
                        value: Army.selected.heroes[i].heroId
                    })))
            );
        }

        this.show(army);
        this.ok(Websocket.split);
        this.cancel();

    },
    armyStatus: function () {
        if (typeof Army.selected == 'undefined') {
            return;
        }

        var army = $('<div>').addClass('status').css('max-height', documentHeight - 200 + 'px');
        var numberOfUnits = 0;
        var bonusTower = 0;
        var castleDefense = getMyCastleDefenseFromPosition(Army.selected.x, Army.selected.y);
        var attackPoints;
        var defensePoints;

        if (isTowerAtPosition(Army.selected.x, Army.selected.y)) {
            bonusTower = 1;
        }
        for (i in Army.selected.soldiers) {
            numberOfUnits++;
            var img = units[Army.selected.soldiers[i].unitId].name.replace(' ', '_').toLowerCase();
            attackPoints = $('<p>').html(units[Army.selected.soldiers[i].unitId].attackPoints).css('color', '#da8');
            defensePoints = $('<p>').html(units[Army.selected.soldiers[i].unitId].defensePoints).css('color', '#da8');
            if (Army.selected.flyBonus && !Army.selected.soldiers[i].canFly) {
                attackPoints.append($('<span>').html(' +1').css('color', '#00ff00'));
                defensePoints.append($('<span>').html(' +1').css('color', '#00ff00'));
            }
            if (Army.selected.heroKey) {
                attackPoints.append($('<span>').html(' +1').css('color', '#00ff00'));
                defensePoints.append($('<span>').html(' +1').css('color', '#00ff00'));
            }
            if (bonusTower) {
                defensePoints.append($('<span>').html(' +1').css('color', '#00ff00'));
            }
            if (castleDefense) {
                defensePoints.append($('<span>').html(' +' + castleDefense).css('color', '#00ff00'));
            }
            army.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Unit.getImageByName(img, Army.selected.color),
                            'id': 'unit' + Army.selected.soldiers[i].soldierId
                        })
                    ))
                    .append(
                        $('<div>').addClass('left')
                            .append($('<p>').html('Current moves: '))
                            .append($('<p>').html('Default moves: '))
                            .append($('<p>').html('Attack points: '))
                            .append($('<p>').html('Defense points: '))
                    )
                    .append(
                        $('<div>').addClass('left')
                            .append($('<p>').html(Army.selected.soldiers[i].movesLeft).css('color', '#da8'))
                            .append($('<p>').html(units[Army.selected.soldiers[i].unitId].numberOfMoves).css('color', '#da8'))
                            .append(attackPoints)
                            .append(defensePoints)
                    )
            );
        }
        for (i in Army.selected.heroes) {
            numberOfUnits++;
            attackPoints = $('<p>').html(Army.selected.heroes[i].attackPoints).css('color', '#da8');
            defensePoints = $('<p>').html(Army.selected.heroes[i].defensePoints).css('color', '#da8');
            if (bonusTower) {
                defensePoints.append($('<span>').html(' +1').css('color', '#d00000'));
            }
            if (castleDefense) {
                defensePoints.append($('<span>').html(' +' + castleDefense).css('color', '#d00000'));
            }
            army.append(
                $('<div>')
                    .addClass('row')
                    .append($('<div>').addClass('nr').html(numberOfUnits))
                    .append($('<div>').addClass('img').html(
                        $('<img>').attr({
                            'src': Hero.getImage(Army.selected.color),
                            'id': 'hero' + Army.selected.heroes[i].heroId
                        })
                    ))
                    .append(
                        $('<div>').addClass('left')
                            .append($('<p>').html('Current moves: '))
                            .append($('<p>').html('Default moves: '))
                            .append($('<p>').html('Attack points: '))
                            .append($('<p>').html('Defense points: '))
                    )
                    .append(
                        $('<div>').addClass('left')
                            .append($('<p>').html(Army.selected.heroes[i].movesLeft).css('color', '#da8'))
                            .append($('<p>').html(Army.selected.heroes[i].numberOfMoves).css('color', '#da8'))
                            .append(attackPoints)
                            .append(defensePoints)
                    )

            );
        }

        this.show(army);
        this.ok();
    },
    battle: function (data, computer) {
        var battle = data.battle;
        var attackerColor = data.attackerColor;
        var defenderColor = data.defenderColor;
        var newBattle = new Array();
        var attack = $('<div>').addClass('battle attack');

        for (i in battle.attack.soldiers) {
            if (battle.attack.soldiers[i].succession) {
                newBattle[battle.attack.soldiers[i].succession] = {
                    'soldierId': battle.attack.soldiers[i].soldierId
                };
            }
            attack.append(
                $('<div>')
                    .attr('id', 'unit' + battle.attack.soldiers[i].soldierId)
                    .css('background', 'url(' + Unit.getImage(battle.attack.soldiers[i].unitId, attackerColor) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }
        for (i in battle.attack.heroes) {
            if (battle.attack.heroes[i].succession) {
                newBattle[battle.attack.heroes[i].succession] = {
                    'heroId': battle.attack.heroes[i].heroId
                };
            }
            attack.append(
                $('<div>')
                    .attr('id', 'hero' + battle.attack.heroes[i].heroId)
                    .css('background', 'url(' + Hero.getImage(attackerColor) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }

        var defense = $('<div>').addClass('battle defense');

        for (i in battle.defense.soldiers) {
            if (battle.defense.soldiers[i].succession) {
                newBattle[battle.defense.soldiers[i].succession] = {
                    'soldierId': battle.defense.soldiers[i].soldierId
                };
            }
            defense.append(
                $('<div>')
                    .attr('id', 'unit' + battle.defense.soldiers[i].soldierId)
                    .css('background', 'url(' + Unit.getImage(battle.defense.soldiers[i].unitId, defenderColor) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }

        for (i in battle.defense.heroes) {
            if (battle.defense.heroes[i].succession) {
                newBattle[battle.defense.heroes[i].succession] = {
                    'heroId': battle.defense.heroes[i].heroId
                };
            }
            defense.append(
                $('<div>')
                    .attr('id', 'hero' + battle.defense.heroes[i].heroId)
                    .css('background', 'url(' + Hero.getImage(defenderColor) + ') no-repeat')
                    .addClass('battleUnit')
            );
        }

        var div = $('<div>')
            .append(attack)
            .append($('<p id="vs">').html('VS').addClass('center'))
            .append(defense)
            .append($('<div>').addClass('battle defense'))
            .append($('<div id="battleOk">').addClass('button buttonColors go').html('OK'));

        this.show(div);

        if (my.color == data.attackerColor && isDigit(data.castleId) && isTruthful(data.victory)) {
            $('#battleOk').click(function () {
                Message.castle(data.castleId);
            });
        } else {
            $('#battleOk').click(function () {
                Message.remove();
            });
        }
        if (newBattle) {
            $('.message').fadeIn(100, function () {
                Message.kill(newBattle, data, computer);
            })
        }
    },
    kill: function (b, data, computer) {
        for (i in b) {
            break;
        }
        if (notSet(b[i])) {
            Move.end(data, computer);
            return;
        }

        Sound.play('error');

        if (isSet(b[i].soldierId)) {
            $('#unit' + b[i].soldierId).append($('<div>').addClass('killed'));
            $('#unit' + b[i].soldierId + ' .killed').fadeIn(1500, function () {
                delete b[i];
                Message.kill(b, data, computer);
            });
        } else if (isSet(b[i].heroId)) {
            $('#hero' + b[i].heroId).append($('<div>').addClass('killed'));
            $('#hero' + b[i].heroId + ' .killed').fadeIn(1500, function () {
                delete b[i];
                Message.kill(b, data, computer);
            });
        } else {
            console.log('zonk');
        }
    },
    raze: function () {
        if (Army.selected == null) {
            return;
        }
        this.show($('<div>').html('Destroy castle. Are you sure?'));
        this.ok(Websocket.raze);
        this.cancel();
    },
    statistics: function () {
        var statistics = $('<div>')
            .append($('<h3>').html('Statistics'));
        var table = $('<table>')
            .addClass('statistics')
            .append($('<tr>')
//                .append($('<th>').html('Players'))
                .append($('<th>').addClass('Players'))
                .append($('<th>').html('Castles conquered'))
                .append($('<th>').html('Castles lost'))
                .append($('<th>').html('Castles razed'))
                .append($('<th>').html('Units created'))
                .append($('<th>').html('Units killed'))
                .append($('<th>').html('Units lost'))
                .append($('<th>').html('Heroes killed'))
                .append($('<th>').html('Heroes lost'))
            );
        for (i in players) {
            var tr = $('<tr>');

            var td = $('<td>').addClass('shortName');
            tr.append(td.html(mapPlayersColors[i].longName))

            var td = $('<td>').css({
                'background': mapPlayersColors[i].backgroundColor,
                'color': mapPlayersColors[i].textColor
            });
            if (isSet(castlesConquered.winners[i])) {
                tr.append(td.html(castlesConquered.winners[i]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'background': mapPlayersColors[i].backgroundColor,
                'color': mapPlayersColors[i].textColor
            });
            if (isSet(castlesConquered.losers[i])) {
                tr.append(td.html(castlesConquered.losers[i]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'background': mapPlayersColors[i].backgroundColor,
                'color': mapPlayersColors[i].textColor
            });
            if (isSet(castlesDestroyed[i])) {
                tr.append(td.html(castlesConquered[i]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'background': mapPlayersColors[i].backgroundColor,
                'color': mapPlayersColors[i].textColor
            });
            if (isSet(soldiersCreated[i])) {
                tr.append(td.html(soldiersCreated[i]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'background': mapPlayersColors[i].backgroundColor,
                'color': mapPlayersColors[i].textColor
            });
            if (isSet(soldiersKilled.winners[i])) {
                tr.append(td.html(soldiersKilled.winners[i]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'background': mapPlayersColors[i].backgroundColor,
                'color': mapPlayersColors[i].textColor
            });
            if (isSet(soldiersKilled.losers[i])) {
                tr.append(td.html(soldiersKilled.losers[i]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'background': mapPlayersColors[i].backgroundColor,
                'color': mapPlayersColors[i].textColor
            });
            if (isSet(heroesKilled.winners[i])) {
                tr.append(td.html(heroesKilled.winners[i]))
            } else {
                tr.append(td.html('0'))
            }

            var td = $('<td>').css({
                'background': mapPlayersColors[i].backgroundColor,
                'color': mapPlayersColors[i].textColor
            });
            if (isSet(heroesKilled.losers[i])) {
                tr.append(td.html(heroesKilled.losers[i]))
            } else {
                tr.append(td.html('0'))
            }

            table.append(tr);
        }
        statistics.append(table);

        var div = $('<div>')
            .addClass('overflow')
            .append(statistics);
        this.show(div);
        this.ok();

        var divHeight = parseInt($('.message').css('height')) - 60;
        $('.message div.overflow').css('height', divHeight + 'px')
    },
    end: function () {
        this.simple('GAME OVER');
    }
}
