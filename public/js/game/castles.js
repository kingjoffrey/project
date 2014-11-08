// *** CASTLES ***

var Castle = {
    handle: function (stop, relocation) {
        var castleId = $('.production').attr('id');

        if (!castleId) {
            Message.error('No castle ID');
            return;
        }

        var unitId = $('input:radio[name=production]:checked').val()

        if (relocation) {
            if (!unitId) {
                Message.error('No unit selected')
            } else {
                Message.simple(translations.relocation, translations.selectCastleToWhichYouWantToRelocateThisProduction)
                $('.castle.' + game.me.color)
                    .unbind()
                    .click(function () {
                        var relocationToCastleId = $(this).attr('id').substring(6);
                        Websocket.production(castleId, unitId, relocationToCastleId);
                    });
            }
            return;
        }

        if (stop) {
            var unitId = -1
        }

        if (unitId) {
            Websocket.production(castleId, unitId);
            return;
        }
    },
    myMousedown: function (el, castleId) {
        el
            .unbind()
            .mousedown(function (e) {
                switch (e.which) {
                    case 1:
                        if (!Army.isSelected) {
                            Message.castle(castleId)
                        }
                }
            });
    },
    updateMyProduction: function (unitId, castleId, relocationToCastleId) {
        if (isTruthful(relocationToCastleId)) {
            Message.simple(translations.production, translations.productionRelocated)
        } else {
            if (unitId === null) {
                Message.simple(translations.production, translations.productionStopped)
            } else {
                Message.simple(translations.production, translations.productionSet)
            }
        }

        for (i in castles) {
            Castle.removeRelocationFrom(i, castleId)
            if (isSet(castles[i].relocatedProduction) && isSet(castles[i].relocatedProduction[castleId])) {
                delete castles[i].relocatedProduction[castleId]
            }
        }

        if (isSet(castles[castleId].relocationToCastleId)) {
            delete castles[castleId].relocationToCastleId
        }

        if (unitId === null) {
            Castle.removeHammer(castleId)
        } else {
            Castle.addHammer(castleId)
        }

        castles[castleId].currentProductionId = unitId;
        castles[castleId].currentProductionTurn = 0;

        if (relocationToCastleId) {

            for (i in castles) {
                Castle.removeRelocationFrom(i, relocationToCastleId)
            }

            Castle.addRelocationTo(castleId)
            Castle.addRelocationFrom(relocationToCastleId, castleId)

            castles[castleId].relocationToCastleId = relocationToCastleId

            $('.castle.' + game.me.color).each(function () {
                var thisCastleId = $(this).attr('id').substring(6);

                Castle.game.meMousedown($(this), thisCastleId)

                if (isSet(castles[thisCastleId].relocatedProduction) && isSet(castles[thisCastleId].relocatedProduction[castleId])) {
                    delete castles[thisCastleId].relocatedProduction[castleId]
                }
            })

            if (notSet(castles[relocationToCastleId].relocatedProduction)) {
                castles[relocationToCastleId].relocatedProduction = {};
            }
            castles[relocationToCastleId].relocatedProduction[castleId] = {
                'currentProductionId': castles[castleId].currentProductionId,
                'currentProductionTurn': castles[castleId].currentProductionTurn
            }
        } else {
            Castle.removeRelocationTo(castleId)
            Castle.removeRelocationFrom(relocationToCastleId)
        }
    },
    initMyProduction: function (castleId) {
        castles[castleId].currentProductionId = players[game.me.color].castles[castleId].productionId;
        castles[castleId].currentProductionTurn = players[game.me.color].castles[castleId].productionTurn;

        var relocationToCastleId = players[game.me.color].castles[castleId].relocationCastleId;

        if (relocationToCastleId) {
            castles[castleId].relocationToCastleId = relocationToCastleId

            if (notSet(castles[relocationToCastleId].relocatedProduction)) {
                castles[relocationToCastleId].relocatedProduction = {};
            }

            castles[relocationToCastleId].relocatedProduction[castleId] = {
                'currentProductionId': castles[castleId].currentProductionId,
                'currentProductionTurn': castles[castleId].currentProductionTurn
            }

            Castle.addRelocationTo(castleId)
            Castle.addRelocationFrom(relocationToCastleId, castleId)
        }

        if (castles[castleId].currentProductionId) {
            Castle.addHammer(castleId);
        }
    },
    addName: function (castleId, name) {
        $('#castle' + castleId).append($('<div>').html(name).addClass('name'));
    },
    addCrown: function (castleId) {
        $('#castle' + castleId).append($('<img>').attr('src', '/img/game/crown.png').addClass('crown'));
    },
    removeCrown: function (castleId) {
        $('#castle' + castleId + ' .crown').remove();
    },
    addShield: function (castleId, defense) {
        $('#castle' + castleId).append($('<div>').css('background', 'url(/img/game/shield.png)').addClass('shield').html(defense));
    },
    addHammer: function (castleId) {
        $('#castle' + castleId).append($('<img>').attr('src', '/img/game/hammer.png').addClass('hammer'));
    },
    removeHammer: function (castleId) {
        $('#castle' + castleId + ' .hammer').remove();
    },
    addRelocationTo: function (castleId) {
        $('#castle' + castleId).append($('<img>').attr('src', '/img/game/relocation_out.png').addClass('relocation_out'))
    },
    removeRelocationTo: function (castleId) {
        $('#castle' + castleId + ' .relocation_out').remove();
    },
    addRelocationFrom: function (castleId, fromCastleId) {
        $('#castle' + castleId).append(
            $('<img>')
                .attr({
                    src: '/img/game/relocation_in.png',
                    id: fromCastleId
                })
                .addClass('relocation_in')
        )
    },
    removeRelocationFrom: function (castleId, fromCastleId) {
        if (isSet(fromCastleId)) {
            $('#castle' + castleId + ' #' + fromCastleId + '.relocation_in').remove();
        } else {
            $('#castle' + castleId + ' .relocation_in').remove();
        }
    },
    show: function () {
        if (Army.selected == null) {
            return;
        }
        var castleId = this.getMy(Army.selected.x, Army.selected.y);
        if (castleId) {
            Army.deselect();
            Message.castle(castleId);
        }
    },
    get: function (x, y) {
        for (castleId in castles) {
            var pos = castles[castleId].position;
            if ((x >= pos.x) && (x < (pos.x + 2)) && (y >= pos.y) && (y < (pos.y + 2))) {
                return castleId;
            }
        }
        return false;
    },
    getMy: function (x, y) {
        for (castleId in castles) {
            if (castles[castleId].color != game.me.color) {
                continue;
            }
            var pos = castles[castleId].position;
            if ((x >= pos.x) && (x < (pos.x + 2)) && (y >= pos.y) && (y < (pos.y + 2))) {
                return castleId;
            }
        }
        return false;
    },
    getEnemy: function (x, y) {
        for (castleId in castles) {
            if (castles[castleId].color == game.me.color) {
                continue;
            }
            var pos = castles[castleId].position;
            if ((x >= pos.x) && (x < (pos.x + 2)) && (y >= pos.y) && (y < (pos.y + 2))) {
                return castleId;
            }
        }
        return null;
    },
    countMyCastles: function () {
        var castleId,
            myCastles = 0
        for (castleId in castles) {
            if (castles[castleId].color == game.me.color) {
                myCastles++
            }
        }
        return myCastles
    },
    changeFields: function (castleId, type, x, y) {
        fields[y][x] = type;
        fields[y + 1][x] = type;
        fields[y][x + 1] = type;
        fields[y + 1][x + 1] = type;
    },
    createNeutral: function (castleId) {
        var castle = game.neutralCastles[castleId]
        Castle.create(castle, castleId)
        Castle.changeFields(castleId, 'e', castle.x, castle.y);
    },
    createWithColor: function (castleId, color) {
        var castle = game.players[color].castles[castleId]
        Castle.create(castle, castleId)
        Castle.owner(castle, castleId, color)
    },
    create: function (castle, castleId) {
        board.append(
            $('<div>')
                .addClass('castle')
                .attr({
                    id: 'castle' + castleId,
                    title: castle.name + ' (' + castle.defense + ')'
                })
                .css({
                    left: (castle.x * 40) + 'px',
                    top: (castle.y * 40) + 'px'
                })
                .mouseover(function () {
                    Castle.changeFields(castleId, 'g', castle.x, castle.y)
                })
                .mouseout(function () {
                    Castle.changeFields(castleId, 'e', castle.x, castle.y)
                })
        );

        Castle.addShield(castleId, castle.defense);
        Castle.addName(castleId, castle.name);

        map.append(
            $('<div>').css({
                'left': castle.x * 2 + 'px',
                'top': castle.y * 2 + 'px'
            })
                .attr('id', 'c' + castleId)
                .addClass('c')
        );
    },
    owner: function (castle, castleId, color) {
        var el = $('#castle' + castleId)
            .removeClass()
            .addClass('castle ' + color)
            .css('background', 'url(/img/game/castles/' + color + '.png) center center no-repeat');

        Castle.removeCrown(castleId)
        Castle.removeHammer(castleId)
        Castle.removeRelocationTo(castleId)
        Castle.removeRelocationFrom(castleId)

        //if (isTruthful(castles[castleId].currentProductionId)) {
        //    castles[castleId].currentProductionId = null
        //}
        //if (isTruthful(castles[castleId].relocationToCastleId)) {
        //    Castle.removeRelocationFrom(castles[castleId].relocationToCastleId, castleId)
        //    castles[castleId].relocationToCastleId = null
        //}
        //
        //if (isTruthful(castles[castleId].relocatedProduction)) {
        //    var relocationFromCastleId
        //    for (relocationFromCastleId in castles[castleId].relocatedProduction) {
        //        Castle.removeRelocationTo(relocationFromCastleId)
        //    }
        //}

        //castles[castleId].defense -= 1;
        //if (castles[castleId].defense < 1) {
        //    castles[castleId].defense = 1;
        //}
        //el.attr('title', castles[castleId].name + '(' + castles[castleId].defense + ')');
        //$('#castle' + castleId + ' .shield').html(castles[castleId].defense);

        if (color == game.me.color) {
            Castle.changeFields(castleId, 'c', castle.x, castle.y)
            el
                .css({
                    'cursor': 'url(/img/game/cursor_castle.png), default'
                })
                .removeClass('team')
            Castle.myMousedown(el, castleId)
        } else {
            if (game.players[color].team == game.players[game.me.color].team) {
                Castle.changeFields(castleId, 'c', castle.x, castle.y)
                el
                    .unbind()
                    .addClass('team')
            } else {
                Castle.changeFields(castleId, 'e', castle.x, castle.y)

                el
                    .unbind()
                    .removeClass('team')
                    .mouseover(function () {
                        Castle.changeFields(castleId, 'E', castle.x, castle.y)
                    }(this))
                    .mouseout(function () {
                        Castle.changeFields(castleId, 'e', castle.x, castle.y)
                    }(this))
                //if (isSet(castles[castleId].relocationToCastleId)) {
                //    delete castles[castles[castleId].relocationToCastleId].relocatedProduction[castleId]
                //    delete castles[castleId].relocationToCastleId
                //}
            }
        }

        if (castle.capital && game.capitals[color] == castleId) {
            Castle.addCrown(castleId);
        }

        $('#c' + castleId).css({
            'background': game.players[color].miniMapColor,
            'border-color': game.players[color].textColor
        })
    },
    raze: function (castleId) {
        if (castles[castleId].color == game.me.color) {
            incomeIncrement(-castles[castleId].income)
        }
        Castle.changeFields(castleId, 'g')
        $('#castle' + castleId).remove();
        $('#c' + castleId).remove();
        delete castles[castleId];
    },
    showFirst: function () {
        if ($('#castle' + game.capitals[game.me.color]).length) {
            var sp = $('#castle' + game.capitals[game.me.color]);
            zoom.lens.setcenter(sp.css('left'), sp.css('top'));
        } else if ($('#castle' + firstCastleId).length) {
            var sp = $('#castle' + firstCastleId);
            zoomer.lensSetCenter(sp.css('left'), sp.css('top'));
        } else {
            Army.showFirst(game.me.color);
        }
    },
    updateDefense: function (castleId, defenseMod) {
        castles[castleId].defense = castles[castleId].defensePoints + defenseMod;
        if (castles[castleId].defense < 1) {
            castles[castleId].defense = 1;
        }
        $('#castle' + castleId).attr('title', castles[castleId].name + '(' + castles[castleId].defense + ')');
        $('#castle' + castleId + ' .shield').html(castles[castleId].defense);
    },
    updateCurrentProductionTurn: function (castleId, productionTurn) {
        castles[castleId].currentProductionTurn = productionTurn;
    },
    selectedArmyCursor: function () {
//        $('.castle:not(.' + game.me.color + '), .castle:not(.team)').css('cursor', 'url(/img/game/cursor_attack.png), crosshair')
//        $('.castle:not(.' + game.me.color + ') .name, .castle:not(.team) .name').css('cursor', 'url(/img/game/cursor.png), default')
        $('.castle:not(.team)').css('cursor', 'url(/img/game/cursor_attack.png), crosshair')
        $('.castle:not(.team) .name').css('cursor', 'url(/img/game/cursor.png), default')
    },
    deselectedArmyCursor: function () {
        $('.castle:not(.' + game.me.color + ' .team)').css('cursor', 'url(/img/game/cursor.png), default');
    },
    myCursor: function () {
        $('.castle.' + game.me.color).css('cursor', 'url(/img/game/cursor_castle.png), crosshair');
    },
    myRemoveCursor: function () {
        $('.castle.' + game.me.color).css('cursor', 'url(/img/game/cursor.png), default');
    }
}


function getMyCastleDefenseFromPosition(x, y) {
    var castleId
    for (castleId in castles) {
        if (castles[castleId].color == game.me.color) {
            var pos = castles[castleId].position;
            if ((x >= pos.x) && (x < (pos.x + 2)) && (y >= pos.y) && (y < (pos.y + 2))) {
                return castles[castleId].defense;
            }
        }
    }
    return 0;
}


