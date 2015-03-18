var Castle = function (castle, bgC, miniMapColor, textColor) {
    var mesh = Three.addCastle(castle.x, castle.y, bgC, castle.defense),
        bgColor = bgC

    map.append(
        $('<div>').css({
            'left': castle.x * 2 + 'px',
            'top': castle.y * 2 + 'px',
            'background': miniMapColor,
            'border-color': textColor
        })
            .attr('id', 'c' + castle.id)
            .addClass('c')
    )

    this.toArray = function () {
        return castle
    }
    this.getMesh = function () {
        return mesh
    }
    this.setProductionId = function (value) {
        castle.productionId = value
    }
    this.setProductionTurn = function (value) {
        castle.productionTurn = value
    }
    this.setRelocationCastleId = function (value) {
        castle.relocationCastleId = value
    }
    this.getX = function () {
        return castle.x
    }
    this.getY = function () {
        return castle.y
    }
    this.getName = function () {
        return castle.name
    }
    this.getIncome = function () {
        return castle.income
    }
    this.getDefense = function () {
        return castle.defense
    }
    this.getCapital = function () {
        return castle.capital
    }
    this.getProduction = function () {
        return castle.production
    }
    this.getProductionId = function () {
        return castle.productionId
    }
    this.getProductionTurn = function () {
        return castle.productionTurn
    }
    this.getRelocationCastleId = function () {
        return castle.relocationCastleId
    }
    this.getCastleId = function () {
        return castle.id
    }
    this.update = function (bgC, miniMapColor, textColor) {
        bgColor = bgC
        this.setProductionId(null)
        $('#c' + castle.id).css({
            'background': miniMapColor,
            'border-color': textColor
        })
        mesh.children[0].material.color.set(bgColor)
    }
    this.handle = function (stop, relocation) {
        var unitId = $('input:radio[name=production]:checked').val()

        if (relocation) {
            if (!unitId) {
                Message.error('No unit selected')
            } else {
                Message.simple(translations.relocation, translations.selectCastleToWhichYouWantToRelocateThisProduction)
                Me.setSelectedCastleId(castle.id)
                Me.setSelectedUnitId(unitId)
            }
            return
        }

        if (stop) {
            var unitId = -1
        }

        if (unitId) {
            Websocket.production(castle.id, unitId)
            return
        }
    }
    this.setDefense = function (defense) {
        castle.defense = defense
        Three.castleChangeDefense(mesh, defense)
    }
}

var Castleeee = {
    updateMyProduction: function (unitId, castleId, relocationToCastleId) {


        for (var i in game.players[game.me.color].castles) {
            Castle.removeRelocationFrom(i, castleId)
            if (isSet(game.players[game.me.color].castles[i].relocatedProduction) && isSet(game.players[game.me.color].castles[i].relocatedProduction[castleId])) {
                delete game.players[game.me.color].castles[i].relocatedProduction[castleId]
            }
        }

        if (isSet(game.players[game.me.color].castles[castleId].relocationToCastleId)) {
            delete game.players[game.me.color].castles[castleId].relocationToCastleId
        }

        if (unitId === null) {
            Castle.removeHammer(castleId)
        } else {
            Castle.addHammer(castleId)
        }

        game.players[game.me.color].castles[castleId].currentProductionId = unitId;
        game.players[game.me.color].castles[castleId].currentProductionTurn = 0;

        if (relocationToCastleId) {

            for (i in castles) {
                Castle.removeRelocationFrom(i, relocationToCastleId)
            }

            Castle.addRelocationTo(castleId)
            Castle.addRelocationFrom(relocationToCastleId, castleId)

            game.players[game.me.color].castles[castleId].relocationToCastleId = relocationToCastleId

            $('.castle.' + game.me.color).each(function () {
                var thisCastleId = $(this).attr('id').substring(6);

                Castle.game.meMousedown($(this), thisCastleId)

                if (isSet(game.players[game.me.color].castles[thisCastleId].relocatedProduction) && isSet(game.players[game.me.color].castles[thisCastleId].relocatedProduction[castleId])) {
                    delete game.players[game.me.color].castles[thisCastleId].relocatedProduction[castleId]
                }
            })

            if (notSet(castles[relocationToCastleId].relocatedProduction)) {
                game.players[game.me.color].castles[relocationToCastleId].relocatedProduction = {};
            }
            castles[relocationToCastleId].relocatedProduction[castleId] = {
                'currentProductionId': game.players[game.me.color].castles[castleId].currentProductionId,
                'currentProductionTurn': game.players[game.me.color].castles[castleId].currentProductionTurn
            }
        } else {
            Castle.removeRelocationTo(castleId)
            Castle.removeRelocationFrom(relocationToCastleId)
        }
    },
    initMyProduction: function (castleId) {
        var relocationToCastleId = game.players[game.me.color].castles[castleId].relocationCastleId;

        if (relocationToCastleId) {
            if (notSet(game.players[game.me.color].castles[relocationToCastleId].relocatedProduction)) {
                game.players[game.me.color].castles[relocationToCastleId].relocatedProduction = {};
            }

            game.players[game.me.color].castles[relocationToCastleId].relocatedProduction[castleId] = {
                'currentProductionId': game.players[game.me.color].castles[castleId].currentProductionId,
                'currentProductionTurn': game.players[game.me.color].castles[castleId].currentProductionTurn
            }

            Castle.addRelocationTo(castleId)
            Castle.addRelocationFrom(relocationToCastleId, castleId)
        }

        if (game.players[game.me.color].castles[castleId].currentProductionId) {
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
        for (var color in game.players) {
            for (var castleId in game.players[color].castles) {
                if (Castle.isCastleAtPosition(x, y, game.players[color].castles[castleId].x, game.players[color].castles[castleId].y)) {
                    return castleId;
                }
            }
        }
        return false;
    },
    getMy: function (x, y) {
        for (var castleId in game.players[game.me.color].castles) {
            if (Castle.isCastleAtPosition(x, y, game.players[game.me.color].castles[castleId].x, game.players[game.me.color].castles[castleId].y)) {
                return castleId;
            }
        }
        return false;
    },
    getEnemy: function (x, y) {
        for (var color in game.players) {
            if (color == game.me.color) {
                continue;
            }
            for (var castleId in game.players[color].castles) {
                if (Castle.isCastleAtPosition(x, y, game.players[color].castles[castleId].x, game.players[color].castles[castleId].y)) {
                    return castleId;
                }
            }
        }
        return null;
    },
    isCastleAtPosition: function (x1, y1, x2, y2) {
        if ((x1 >= x2) && (x1 < (x2 + 2)) && (y1 >= y2) && (y1 < (y2 + 2))) {
            return true;
        }
    },
    countMyCastles: function () {
        var myCastles = 0
        for (var castleId in game.players[game.me.color].castles) {
            myCastles++
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
        Castle.create(game.players[color].castles[castleId], castleId)
        Castle.owner(castleId, color)
    },
    owner: function (castleId, newColor, oldColor) {
        var el = $('#castle' + castleId)
            .removeClass()
            .addClass('castle ' + color)
            .css('background', 'url(/img/game/castles/' + newColor + '.png) center center no-repeat');

        if (isSet(oldColor)) {
            game.players[newColor].castles[castleId] = game.players[oldColor].castles[castleId]
            delete game.players[oldColor].castles[castleId]

            if (oldColor == game.me.color) {
                Castle.removeHammer(castleId)
                Castle.removeRelocationTo(castleId)
                Castle.removeRelocationFrom(castleId)
            }
            Castle.removeCrown(castleId)
        }

        var castle = game.players[newColor].castles[castleId]


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

        if (newColor == game.me.color) {
            Castle.changeFields(castleId, 'c', castle.x, castle.y)
            el
                .css({
                    'cursor': 'url(/img/game/cursor_castle.png), default'
                })
                .removeClass('team')
            Castle.myMousedown(el, castleId)
        } else {
            if (game.players[newColor].team == game.players[game.me.color].team) {
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
                if (castle.relocationToCastleId) {
                    delete game.players[newColor].castles[castle.relocationToCastleId].relocatedProduction[castleId]
                    castle.relocationToCastleId = null
                }
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
    }
}
