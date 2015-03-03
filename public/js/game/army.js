var Army = function (army, bgColor, miniMapColor, textColor, color) {
    var meshId = Three.addArmy(army.x, army.y, bgColor, army.id),
        heroSplitKey = null,
        soldierSplitKey = null
    Fields.get(army.x, army.y).addArmyId(army.id, color)
    map.append(
        $('<div>')
            .css({
                'left': army.x * 2 + 'px',
                'top': army.y * 2 + 'px',
                'background': miniMapColor,
                'border-color': textColor,
                'z-index': 10
            })
            .attr('id', army.id)
            .addClass('a')
    )
    this.skippedHeroes = {}
    this.skippedSoldiers = {}
    this.update = function (a) {
        console.log(a)
        console.log('NEW ARMY DATA armyId=' + a.id)
        console.log(army)
        console.log('OLD ARMY DATA armyId=' + army.id)
        Fields.get(army.x, army.y).removeArmyId(army.id)
        //if (isSet(a.x)) {
        //    army.x = a.x
        //}
        //if (isSet(a.y)) {
        //    army.y = a.y
        //}
        for (var key in a) {
            if (key == 'soldiers') {
                for (var soldierId in army.soldiers) {
                    if (!isTruthful(a.soldiers[soldierId])) {
                        delete army.soldiers[soldierId]
                    }
                }
                for (var soldierId in a.soldiers) {
                    if (a.soldiers[soldierId]) {
                        army.soldiers[soldierId] = a.soldiers[soldierId]
                    }
                }
            } else if (key == 'heroes') {
                for (var heroId in army.heroes) {
                    if (!isTruthful(a.heroes[heroId])) {
                        delete army.heroes[heroId]
                    }
                }
                for (var heroId in a.heroes) {
                    if (a.heroes[heroId]) {
                        army.heroes[heroId] = a.heroes[heroId]
                    }
                }
            }
            army[key] = a[key]
        }
        Fields.get(army.x, army.y).addArmyId(army.id, color)
    }
    this.getMeshId = function () {
        return meshId
    }
    this.getArmyId = function () {
        return army.id
    }
    this.getX = function () {
        return army.x
    }
    this.getY = function () {
        return army.y
    }
    this.getMoves = function () {
        var moves
        for (var i in  army.soldiers) {
            if (notSet(moves)) {
                moves = army.soldiers[i].movesLeft
            }
            if (moves > army.soldiers[i].movesLeft) {
                moves = army.soldiers[i].movesLeft
            }
        }
        for (var i in army.heroes) {
            if (notSet(moves)) {
                moves = army.heroes[i].movesLeft
            }
            if (moves > army.heroes[i].movesLeft) {
                moves = army.heroes[i].movesLeft
            }
        }
        return moves
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
            return 'swimming';
        } else if (army.canFly > 0) {
            return 'flying'
        } else {
            return 'walking'
        }
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
    this.setPosition = function (x, y) {
        Three.getScene().getObjectById(meshId).position.set(x * 4 - 216, 0, y * 4 - 311)
    }
    this.getNumberOfUnits = function () {
        var numberOfUnits = countProperties(army.heroes) + countProperties(army.soldiers)

        if (numberOfUnits > 8) {
            numberOfUnits = 8
        }
        return numberOfUnits
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