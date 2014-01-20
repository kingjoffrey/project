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
                Message.simpleNew(translations.relocation, translations.selectCastleToWhichYouWantToRelocateThisProduction)
                $('.castle.' + my.color)
                    .unbind('click')
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
    updateMyProduction: function (unitId, castleId, relocationToCastleId) {
        if (isTruthful(relocationToCastleId)) {
            Message.simpleNew(translations.production, translations.productionRelocated)
        } else {
            if (unitId === null) {
                Message.simpleNew(translations.production, translations.productionStopped)
            } else {
                Message.simpleNew(translations.production, translations.productionSet)
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

            $('.castle.' + my.color).each(function () {
                var thisCastleId = $(this).attr('id').substring(6);

                $(this)
                    .unbind('click')
                    .click(function () {
                        Message.castle(thisCastleId)
                    });

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
        castles[castleId].currentProductionId = players[my.color].castles[castleId].productionId;
        castles[castleId].currentProductionTurn = players[my.color].castles[castleId].productionTurn;

        var relocationToCastleId = players[my.color].castles[castleId].relocationToCastleId;

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
    addName: function (castleId) {
        $('#castle' + castleId).append($('<div>').html(castles[castleId].name).addClass('name'));
    },
    addCrown: function (castleId) {
        $('#castle' + castleId).append($('<img>').attr('src', '/img/game/crown.png').addClass('crown'));
    },
    removeCrown: function (castleId) {
        $('#castle' + castleId + ' .crown').remove();
    },
    addShield: function (castleId) {
        $('#castle' + castleId).append($('<div>').css('background', 'url(/img/game/shield.png)').addClass('shield').html(castles[castleId].defense));
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
            if (castles[castleId].color != my.color) {
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
            if (castles[castleId].color == my.color) {
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
            if (castles[castleId].color == my.color) {
                myCastles++
            }
        }
        return myCastles
    },
    changeFields: function (castleId, type) {
        x = castles[castleId].x;
        y = castles[castleId].y;
        fields[y][x] = type;
        fields[y + 1][x] = type;
        fields[y][x + 1] = type;
        fields[y + 1][x + 1] = type;
    },
    createNeutral: function (castleId) {
        castles[castleId].defense = castles[castleId].defensePoints;
        castles[castleId].color = null;

        board.append(
            $('<div>')
                .addClass('castle')
                .attr({
                    id: 'castle' + castleId,
                    title: castles[castleId].name + ' (' + castles[castleId].defense + ')'
                })
                .css({
                    left: (castles[castleId].x * 40) + 'px',
                    top: (castles[castleId].y * 40) + 'px'
                })
                .mouseover(function () {
                    Castle.changeFields(this.id.substring(6), 'g')
                })
                .mouseout(function () {
                    Castle.changeFields(this.id.substring(6), 'e')
                })
        );

        Castle.addShield(castleId);
        Castle.addName(castleId);

        Castle.changeFields(castleId, 'e');

        map.append(
            $('<div>').css({
                'left': castles[castleId].x * 2 + 'px',
                'top': castles[castleId].y * 2 + 'px'
            })
                .attr('id', 'c' + castleId)
                .addClass('c')
        );
    },
    owner: function (castleId, color) {
        var castle = $('#castle' + castleId)
            .removeClass()
            .addClass('castle ' + color)
            .css('background', 'url(/img/game/castles/' + color + '.png) center center no-repeat');

        if (castles[castleId].color) {
            Castle.removeCrown(castleId)
            Castle.removeHammer(castleId)
            Castle.removeRelocationTo(castleId)
            Castle.removeRelocationFrom(castleId)

            if (isTruthful(castles[castleId].currentProductionId)) {
                castles[castleId].currentProductionId = null
            }
            if (isTruthful(castles[castleId].relocationToCastleId)) {
                Castle.removeRelocationFrom(castles[castleId].relocationToCastleId, castleId)
                castles[castleId].relocationToCastleId = null
            }

            if (isTruthful(castles[castleId].relocatedProduction)) {
                var relocationFromCastleId
                for (relocationFromCastleId in castles[castleId].relocatedProduction) {
                    Castle.removeRelocationTo(relocationFromCastleId)
                }
            }

            castles[castleId].defense -= 1;
            if (castles[castleId].defense < 1) {
                castles[castleId].defense = 1;
            }
            castle.attr('title', castles[castleId].name + '(' + castles[castleId].defense + ')');
            $('#castle' + castleId + ' .shield').html(castles[castleId].defense);
        }

        if (color == my.color) {
            Castle.changeFields(castleId, 'c')
            castle
                .css({
                    'cursor': 'url(/img/game/cursor_castle.png), default'
                })
                .unbind()
                .removeClass('team')
                .click(function () {
                    Message.castle(castleId)
                });
        } else {
            if (players[color].team == my.color) {
                Castle.changeFields(castleId, 'c')
                castle
                    .unbind()
                    .addClass('team')
            } else {
                Castle.changeFields(castleId, 'e')
                castle
                    .unbind()
                    .removeClass('team')
                    .mouseover(function () {
                        Castle.changeFields(this.id.substring(6), 'g')
                    })
                    .mouseout(function () {
                        Castle.changeFields(this.id.substring(6), 'e')
                    })
                if (isSet(castles[castleId].relocationToCastleId)) {
                    delete castles[castles[castleId].relocationToCastleId].relocatedProduction[castleId]
                    delete castles[castleId].relocationToCastleId
                }
            }
        }

        if (castles[castleId].capital && capitals[color] == castleId) {
            Castle.addCrown(castleId);
        }

        castles[castleId].color = color;

        $('#c' + castleId).css({
            'background': players[color].minimapColor,
            'border-color': players[color].textColor
        })
    },
    raze: function (castleId) {
        if (castles[castleId].color == my.color) {
            incomeIncrement(-castles[castleId].income)
        }
        Castle.changeFields(castleId, 'g')
        $('#castle' + castleId).remove();
        $('#c' + castleId).remove();
        delete castles[castleId];
    },
    showFirst: function () {
        if ($('#castle' + capitals[my.color]).length) {
            var sp = $('#castle' + capitals[my.color]);
            zoomer.lensSetCenter(sp.css('left'), sp.css('top'));
        } else if ($('#castle' + firstCastleId).length) {
            var sp = $('#castle' + firstCastleId);
            zoomer.lensSetCenter(sp.css('left'), sp.css('top'));
        } else {
            Army.showFirst(my.color);
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
//        $('.castle:not(.' + my.color + '), .castle:not(.team)').css('cursor', 'url(/img/game/cursor_attack.png), crosshair')
//        $('.castle:not(.' + my.color + ') .name, .castle:not(.team) .name').css('cursor', 'url(/img/game/cursor.png), default')
        $('.castle:not(.team)').css('cursor', 'url(/img/game/cursor_attack.png), crosshair')
        $('.castle:not(.team) .name').css('cursor', 'url(/img/game/cursor.png), default')
    },
    deselectedArmyCursor: function () {
        $('.castle:not(.' + my.color + ' .team)').css('cursor', 'url(/img/game/cursor.png), default');
    },
    myCursor: function () {
        $('.castle.' + my.color).css('cursor', 'url(/img/game/cursor_castle.png), crosshair');
    },
    myRemoveCursor: function () {
        $('.castle.' + my.color).css('cursor', 'url(/img/game/cursor.png), default');
    }
}


function getMyCastleDefenseFromPosition(x, y) {
    var castleId
    for (castleId in castles) {
        if (castles[castleId].color == my.color) {
            var pos = castles[castleId].position;
            if ((x >= pos.x) && (x < (pos.x + 2)) && (y >= pos.y) && (y < (pos.y + 2))) {
                return castles[castleId].defense;
            }
        }
    }
    return 0;
}


