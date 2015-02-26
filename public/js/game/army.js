var Army = function (army, bgColor, miniMapColor, textColor, color) {
    var meshId = Three.addArmy(army.x, army.y, bgColor, army.id),
        heroSplitKey = null,
        soldierSplitKey = null

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
        Fields.get(army.x, army.y).removeArmyId(army.id)
        //if (isSet(a.x)) {
        //    army.x = a.x
        //}
        //if (isSet(a.y)) {
        //    army.y = a.y
        //}
        for (var key in a) {
            army[key] = a[key]
        }
        Fields.get(army.x, army.y).addArmyId(army.id, color)
        //this.getMovementType()
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