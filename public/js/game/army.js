var Army = function (army, bgColor, miniMapColor, textColor, color) {
    var heroSplitKey = null,
        soldierSplitKey = null

    this.skippedHeroes = {}
    this.skippedSoldiers = {}
    this.update = function (a) {
        console.log(a)
        console.log('NEW ARMY DATAAAAAAAAAAAAAAAAAAAAAAA armyId=' + a.id)
        console.log(army)
        console.log('OLD ARMY DATAAAAAAAAAAAAAAAAAAAAAAA armyId=' + army.id)
        Fields.get(army.x, army.y).removeArmyId(army.id)
        //if (isSet(a.x)) {
        //    army.x = a.x
        //}
        //if (isSet(a.y)) {
        //    army.y = a.y
        //}
        for (var key in a) {
            if (key == 'walk') {
                for (var soldierId in army.walk) {
                    if (notSet(a.walk[soldierId])) {
                        delete army.walk[soldierId]
                    }
                }
                for (var soldierId in a.walk) {
                    if (a.walk[soldierId]) {
                        army.walk[soldierId] = a.walk[soldierId]
                    }
                }
            } else if (key == 'swim') {
                for (var soldierId in army.swim) {
                    if (notSet(a.swim[soldierId])) {
                        delete army.swim[soldierId]
                    }
                }
                for (var soldierId in a.swim) {
                    if (a.swim[soldierId]) {
                        army.swim[soldierId] = a.swim[soldierId]
                    }
                }
            } else if (key == 'fly') {
                for (var soldierId in army.fly) {
                    if (notSet(a.fly[soldierId])) {
                        delete army.fly[soldierId]
                    }
                }
                for (var soldierId in a.fly) {
                    if (a.fly[soldierId]) {
                        army.fly[soldierId] = a.fly[soldierId]
                    }
                }
            } else if (key == 'heroes') {
                for (var heroId in army.heroes) {
                    if (notSet(a.heroes[heroId])) {
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
        Three.armyChangeFlag(army.mesh, color, this.getNumberOfUnits())
        $('#' + this.getArmyId() + '.a').css({left: army.x * 2 + 'px', top: army.y * 2 + 'px'})
    }
    this.getMesh = function () {
        return army.mesh
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
        for (var i in  army.walk) {
            if (notSet(moves)) {
                moves = army.walk[i].movesLeft
            }
            if (moves > army.walk[i].movesLeft) {
                moves = army.walk[i].movesLeft
            }
        }
        for (var i in  army.swim) {
            if (notSet(moves)) {
                moves = army.swim[i].movesLeft
            }
            if (moves > army.swim[i].movesLeft) {
                moves = army.swim[i].movesLeft
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
        for (var key in army.walk) {
            return key
        }
    }
    this.getMovementType = function () {
        if (this.canSwim()) {
            return 'swim';
        } else if (this.canFly()) {
            return 'fly'
        } else {
            return 'walk'
        }
    }
    this.canSwim = function () {
        return countProperties(army.swim)
    }
    this.canFly = function () {
        return army.canFly > 0
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
    this.getWalkingSoldiers = function () {
        return army.walk
    }
    this.getWalkingSoldier = function (soldierId) {
        return army.walk[soldierId]
    }
    this.getSwimmingSoldiers = function () {
        return army.swim
    }
    this.getSwimmingSoldier = function (soldierId) {
        return army.swim[soldierId]
    }
    this.getFlyingSoldiers = function () {
        return army.fly
    }
    this.getFlyingSoldier = function (soldierId) {
        return army.fly[soldierId]
    }
    this.setPosition = function (x, y) {
        army.mesh.position.set(x * 4 - 216, 0, y * 4 - 311)
    }
    this.getNumberOfUnits = function () {
        var numberOfUnits = countProperties(army.heroes) + countProperties(army.walk) + countProperties(army.swim) + countProperties(army.fly)

        if (numberOfUnits > 8) {
            numberOfUnits = 8
        }
        return numberOfUnits
    }
    this.getHeroBonus = function () {
        return countProperties(army.heroes)
    }
    this.getFlyBonus = function () {
        return countProperties(army.fly)
    }
    this.deleteSoldier = function (soldierId) {
        delete army.walk[soldierId]
    }
    this.deleteShip = function (soldierId) {
        delete army.swim[soldierId]
    }
    this.deleteHero = function (heroId) {
        delete army.heroes[heroId]
    }
    this.getFortified = function () {
        return army.fortified
    }

    army.mesh = Three.addArmy(army.x, army.y, bgColor, this.getNumberOfUnits())

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