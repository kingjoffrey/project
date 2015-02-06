var Army = function (army, bgColor, miniMapColor, textColor) {
    var meshId = Three.addArmy(army.x, army.y, bgColor, army.armyId),
        heroSplitKey = null,
        soldierSplitKey = null,
        numberOfUnits = countProperties(army.heroes) + countProperties(army.soldiers)

    if (numberOfUnits > 8) {
        numberOfUnits = 8
    }

    map.append(
        $('<div>')
            .css({
                'left': army.x * 2 + 'px',
                'top': army.y * 2 + 'px',
                'background': miniMapColor,
                'border-color': textColor,
                'z-index': 10
            })
            .attr('id', army.armyId)
            .addClass('a')
    )
    this.update = function (a) {
        army = a
        numberOfUnits = countProperties(army.heroes) + countProperties(army.soldiers)
        if (numberOfUnits > 8) {
            numberOfUnits = 8
        }
        //this.getMovementType()
    }
    this.getMeshId = function () {
        return meshId
    }
    this.getX = function () {
        return army.x
    }
    this.getY = function () {
        return army.y
    }
    this.getMoves = function () {
        return army.moves
    }
    this.getHeroKey = function () {
        for (var heroId in army.heroes) {
            return heroId
        }
    }
    this.getSoldierKey = function () {
        for (var key in army.soldiers) {
            return key
        }
    }
    this.getMovementType = function () {
        if (army.canSwim) {
            army.movementType = 'swimming';
            //for (var key in Terrain.toArray()) {
            //    army.terrain[key] = Terrain.get(key)[army.movementType]
            //}
            //
            //for (var key in army.soldiers) {
            //    if (army.soldiers[key].unitId != shipId) {
            //        continue;
            //    }
            //
            //    if (notSet(shipMoves)) {
            //        var shipMoves = army.soldiers[key].movesLeft;
            //    }
            //
            //    if (army.soldiers[key].movesLeft < shipMoves) {
            //        shipMoves = army.soldiers[key].movesLeft
            //    }
            //}
            //
            //army.moves = shipMoves;
        } else if (army.canFly > 0) {
            army.movementType = 'flying';
            //for (key in Terrain.toArray()) {
            //    army.terrain[key] = Terrain.get(key)[army.movementType];
            //}
            //
            //for (key in army.soldiers) {
            //    if (!game.units[army.soldiers[key].unitId].canFly) {
            //        continue;
            //    }
            //
            //    if (notSet(flyMoves)) {
            //        var flyMoves = army.soldiers[key].movesLeft;
            //    }
            //
            //    if (army.soldiers[key].movesLeft < flyMoves) {
            //        flyMoves = army.soldiers[key].movesLeft
            //    }
            //}

            //army.moves = flyMoves;
        } else {
            army.movementType = 'walking';
            //for (key in Terrain.toArray()) {
            //    army.terrain[key] = Terrain.get(key)[army.movementType];
            //}
            //
            //if (typeof army.heroes === 'object') {
            //    var f = army.terrain.f,
            //        m = army.terrain.m,
            //        s = army.terrain.s;
            //} else {
            //    var f = 0,
            //        m = 0,
            //        s = 0;
            //}
            //
            //for (key in army.heroes) {
            //    if (notSet(moves)) {
            //        var moves = army.heroes[key].movesLeft;
            //    }
            //
            //    if (army.heroes[key].movesLeft < moves) {
            //        moves = army.heroes[key].movesLeft
            //    }
            //}
            //
            //for (key in army.soldiers) {
            //    if (Units.get(army.soldiers[key].unitId).f > f) {
            //        f = Units.get(army.soldiers[key].unitId).f;
            //    }
            //    if (Units.get(army.soldiers[key].unitId).m > m) {
            //        m = Units.get(army.soldiers[key].unitId).m;
            //    }
            //    if (Units.get(army.soldiers[key].unitId).s > s) {
            //        s = Units.get(army.soldiers[key].unitId).s;
            //    }
            //
            //    if (notSet(moves)) {
            //        var moves = army.soldiers[key].movesLeft;
            //    }
            //
            //    if (army.soldiers[key].movesLeft < moves) {
            //        moves = army.soldiers[key].movesLeft
            //    }
            //}
            //
            //army.terrain.f = f;
            //army.terrain.m = m;
            //army.terrain.s = s;
            //
            //army.moves = moves;
        }
        return army.movementType
    }
    this.getHeroMovesLeft = function (key) {
        return army.heroes[key].movesLeft
    }
    this.getSoldierMovesLeft = function (key) {
        return army.soldiers[key].movesLeft
    }
    this.countHeroes = function () {
        return army.heroes.length
    }
    this.countSoldiers = function () {
        return army.soldiers.length
    }
    this.setHeroSplitKey = function (value) {
        heroSplitKey = value
    }
    this.setSoldierSplitKey = function (value) {
        soldierSplitKey = value
    }
    this.getHeroSplitKey = function () {
        return heroSplitKey
    }
    this.getSoldierSplitKey = function () {
        return soldierSplitKey
    }
    this.getHeroes = function () {
        return army.heroes
    }
    this.getHero = function (heroId) {
        return army.heroes[heroId]
    }
    this.getSoldiers = function () {
        return army.soldiers
    }
    this.getSoldier = function (soldierId) {
        return army.soldiers[soldierId]
    }
}

var Armyyyy = {
    setImg: function (army, heroKey, soldierKey) {
        if (heroKey) {
            if (army.heroes[heroKey].name) {
                army.name = army.heroes[heroKey].name;
            } else {
                army.name = 'Anonymous hero';
            }

            army.img = Hero.getImage(army.color);
            army.attack = army.heroes[heroKey].attackPoints;
            army.defense = army.heroes[heroKey].defensePoints;
        } else if (soldierKey) {
            if (Units.get(army.soldiers[soldierKey].unitId).name_lang) {
                army.name = Units.get(army.soldiers[soldierKey].unitId).name_lang;
            } else {
                army.name = Units.get(army.soldiers[soldierKey].unitId).name;
            }

            army.img = Unit.getImage(army.soldiers[soldierKey].unitId, army.color);
            army.attack = Units.get(army.soldiers[soldierKey].unitId).attackPoints;
            army.defense = Units.get(army.soldiers[soldierKey].unitId).defensePoints;
        }

        return army;
    },
    changeImg: function (army) {
        $('#army' + army.armyId + ' .unit img').attr('src', army.img);
    },
    init: function (obj, color) {

        $('#army' + obj.armyId).remove();
        $('#' + obj.armyId + '.a').remove();

        if (obj.destroyed) {
            Army.fields(game.players[color].armies[obj.armyId]);
            delete game.players[color].armies[obj.armyId];

            return;
        }

        var army = {
            armyId: obj.armyId,
            x: obj.x,
            y: obj.y,
            flyBonus: 0,
            canFly: obj.canFly,
            canSwim: obj.canSwim,
            heroes: obj.heroes,
            soldiers: obj.soldiers,
            fortified: obj.fortified,
            color: color,
            moves: 0,
            heroKey: this.getHeroKey(obj.heroes),
            soldierKey: this.getSoldierKey(obj.soldiers),
            skippedHeroes: {},
            skippedSoldiers: {},
            terrain: {},
            heroSplitKey: null,
            soldierSplitKey: null
        }

        if (army.fortified) {
            Army.quitedArmies[army.armyId] = 1;
        } else {
            this.unfortify(army.armyId);
        }

        army = this.getMovementType(army)
        army = this.setImg(army, army.heroKey, army.soldierKey)

        var element = $('<div>')
            .addClass('army ' + color)
            .attr({
                id: 'army' + army.armyId,
                title: army.name
            })
            .css({
                left: (army.x * 40 - 1) + 'px',
                top: (army.y * 40 - 1) + 'px'
            });

        if (color == game.me.color) { // moja armia
            element.addClass('team')
            element.click(function (e) {
                Army.myClick(army, e)
            });
            element.mouseover(function () {
                Army.myMouseOver(army.armyId)
            })
            if (army.canSwim) {
                if (!Castle.getMy(army.x, army.y)) {
                    fields[army.y][army.x] = 'S';
                }
            }
        } else { // nie moja armia
            if (game.players[color].team == game.players[game.me.color].team) {
                element.addClass('team')
            } else {
                fields[army.y][army.x] = 'e';
                this.enemyMouse(element, army.x, army.y);
            }
        }


        //board.append(
        //    element
        //        .append(
        //        $('<div>')
        //            .addClass('flag')
        //            .css('background', 'url(/img/game/flags/' + color + '_' + numberOfUnits + '.png) top left no-repeat')
        //            .append(
        //            $('<div>')
        //                .addClass('unit')
        //                .append(
        //                $('<img>')
        //                    .attr('src', army.img)
        //            )
        //        )
        //    )
        //);
    },

    computerLoop: function (armies, color) {
        var armyId;
        for (armyId in armies) {
            break;
        }

        if (notSet(armies[armyId])) {
            Websocket.computer();
            return;
        }

        Army.init(armies[armyId], color);

        delete armies[armyId]; // potrzebne do pętli

        this.computerLoop(armies, color);
    },
    fields: function (a) {
        if (a.color == game.me.color) {
            if (fields[a.y][a.x] == 'S') {
                fields[a.y][a.x] = game.fields[a.y][a.x];
            }
            return;
        }

        if (Castle.getEnemy(a.x, a.y) !== null) {
            fields[a.y][a.x] = 'e';
        } else {
            fields[a.y][a.x] = game.fields[a.y][a.x];
        }
    },
    myMouseOver: function (armyId) {
        if (Gui.lock) {
            return;
        }

//        if (Turn.isMy()) {
        $('#army' + armyId + ' *').css('cursor', 'url(/img/game/cursor_select.png) 12 13, default')
//        } else {
//            $('#army' + armyId + ' *').css('cursor', 'url(/img/game/cursor.png), default')
//        }
    },
    enemyCursorWhenSelected: function () {
//        $('.army:not(.' + game.me.color + ') *').css('cursor', 'url(/img/game/cursor_attack.png) 13 16, crosshair')
        $('.army:not(.team) *').css('cursor', 'url(/img/game/cursor_attack.png) 13 16, crosshair')
    },
    enemyCursorWhenUnselected: function () {
//        $('.army:not(.' + game.me.color + ') *').css('cursor', 'url(/img/game/cursor.png), default');
        $('.army:not(.team) *').css('cursor', 'url(/img/game/cursor.png), default');
    },
    enemyMouse: function (element, x, y) {
        element
            .mouseover(function () {
                if (Gui.lock) {
                    return;
                }
                if (Turn.isMy() && Army.selected) {
                    var castleId = Castle.getEnemy(x, y);
                    if (castleId !== null) {
                        Castle.changeFields(castleId, 'E', x, y);
                    } else {
                        fields[y][x] = 'g';
                    }
                }
            })
            .mouseout(function () {
                var castleId = Castle.getEnemy(x, y);
                if (castleId !== null) {
                    Castle.changeFields(castleId, 'e', x, y);
                } else {
                    fields[y][x] = 'e';
                }

            })
    }
}


// *** UNITS ***

var Unit = {
    getId: function (name) {
        for (var unitId in Units.get) {
            if (Units.get(unitId) != null && Units.get(unitId).name == name) {
                return Units[i].mapUnitId;
            }
        }

        return null;
    },
    getImage: function (unitId, color) {
        return '/img/game/units/' + color + '/' + Units.get(unitId).name.replace(' ', '_').toLowerCase() + '.png'
    },
    getShipId: function () {
        for (i in game.units) {
            if (Units.get(i) == null) {
                continue;
            }
            if (Units.get(i).canSwim) {
                return i;
            }
        }
    }
}

var Hero = {
    getImage: function (color) {
        return '/img/game/heroes/' + color + '.png';
    },
    findMy: function () {
        for (var armyId in Players.get(Me.getColor()).getArmies().toArray()) {
            var heroId = Me.getArmy(armyId).getHeroKey()
            if (heroId) {
                return heroId
            }
        }
    }
}