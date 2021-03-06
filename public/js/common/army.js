var Army = function (army, bgColor, miniMapColor, textColor, color) {
    var heroSplitKey = null,
        soldierSplitKey = null,
        pathMoves = 0

    this.update = function (a) {
        GameScene.remove(army.mesh)
        Fields.get(army.x, army.y).removeArmyId(army.id) // remove armyId from last visited field

        var numberOfUnits = Unit.countNumberOfUnits(a)

        if (!numberOfUnits) { // no sens to update if no units (army will be destroyed)
            return
        }

        army = a

        Fields.get(army.x, army.y).addArmyId(army.id, color)
        army.mesh = GameModels.addArmy(army.x, army.y, bgColor, numberOfUnits, this.getModelName(), this.canSwim(), this.countLife())
    }
    this.toArray = function () {
        return army
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
        if (this.canSwim()) {
            for (var i in  army.swim) {
                if (notSet(moves)) {
                    moves = army.swim[i].movesLeft
                }
                if (moves > army.swim[i].movesLeft) {
                    moves = army.swim[i].movesLeft
                }
            }
        } else if (this.canFly()) {
            for (var i in  army.fly) {
                if (notSet(moves)) {
                    moves = army.fly[i].movesLeft
                }
                if (moves > army.fly[i].movesLeft) {
                    moves = army.fly[i].movesLeft
                }
            }
        } else {
            for (var i in  army.walk) {
                if (notSet(moves)) {
                    moves = army.walk[i].movesLeft
                }
                if (moves > army.walk[i].movesLeft) {
                    moves = army.walk[i].movesLeft
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
        if (this.canSwim() || countProperties(army.walk)) {
            return false
        } else if (countProperties(army.fly) >= countProperties(army.heroes)) {
            return true
        } else {
            return false
        }
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
    this.getHeroBonus = function () {
        return countProperties(army.heroes)
    }
    this.getFlyBonus = function () {
        return countProperties(army.fly)
    }
    this.deleteWalkingSoldier = function (soldierId) {
        delete army.walk[soldierId]
    }
    this.deleteSwimmingSoldier = function (soldierId) {
        delete army.swim[soldierId]
    }
    this.deleteFlyingSoldier = function (soldierId) {
        delete army.fly[soldierId]
    }
    this.deleteHero = function (heroId) {
        delete army.heroes[heroId]
    }
    this.getFortified = function () {
        return army.fortified
    }
    this.setFortified = function (f) {
        army.fortified = f
    }
    this.getModelName = function () {
        var attack = 0,
            name = null

        if (countProperties(army.swim)) {
            for (var soldierId in army.swim) {
                name = Units.get(army.swim[soldierId].unitId).name
                break
            }
        } else if (countProperties(army.heroes)) {
            return 'hero'
        } else {
            for (var soldierId in army.walk) {
                var unit = Units.get(army.walk[soldierId].unitId)
                if (unit.a > attack) {
                    attack = unit.a
                    name = unit.name
                }
            }
            for (var soldierId in army.fly) {
                var unit = Units.get(army.fly[soldierId].unitId)
                if (unit.a > attack) {
                    attack = unit.a
                    name = unit.name
                }
            }
        }

        if (name) {
            return Unit.convertName(name)
        } else {
            kupa()
        }
    }
    this.resetPathMoves = function () {
        for (var i in  army.walk) {
            army.walk[i].pathMoves = army.walk[i].movesLeft
        }
        for (var i in  army.swim) {
            army.swim[i].pathMoves = army.swim[i].movesLeft
        }
        for (var i in  army.fly) {
            army.fly[i].pathMoves = army.fly[i].movesLeft
        }
        for (var i in army.heroes) {
            army.heroes[i].pathMoves = army.heroes[i].movesLeft
        }
    }
    this.pathStep = function (t, movementType) {
        switch (movementType) {
            case 'swim':
                for (var i in  army.swim) {
                    var soldier = army.swim[i]
                    if (soldier.pathMoves - Terrain.get(t)[movementType] < 0) {
                        return true
                    } else {
                        soldier.pathMoves -= Terrain.get(t)[movementType]
                    }
                }
                break;
            case 'fly':
                for (var i in  army.fly) {
                    var soldier = army.fly[i]

                    if (soldier.pathMoves - Terrain.get(t)[movementType] < 0) {
                        return true
                    } else {
                        soldier.pathMoves -= Terrain.get(t)[movementType]
                    }
                }
                break;
            case 'walk':
                for (var i in  army.walk) {
                    var soldier = army.walk[i],
                        unit = Units.get(soldier.unitId)
                    if (isSet(unit[t])) {
                        var moveCost = unit[t]
                    } else {
                        var moveCost = Terrain.get(t)[movementType]
                    }

                    if (soldier.pathMoves - moveCost < 0) {
                        return true
                    } else {
                        soldier.pathMoves -= moveCost
                    }
                }
                for (var i in  army.fly) {
                    var soldier = army.fly[i]

                    if (soldier.pathMoves - Terrain.get(t)[movementType] < 0) {
                        return true
                    } else {
                        soldier.pathMoves -= Terrain.get(t)[movementType]
                    }
                }
                for (var i in  army.heroes) {
                    var hero = army.heroes[i]
                    if (hero.pathMoves - Terrain.get(t)[movementType] < 0) {
                        return true
                    } else {
                        hero.pathMoves -= Terrain.get(t)[movementType]
                    }
                }
                break;
            default :
                throw 1
        }
    }
    this.getTerrainToMoveCostMappings = function () {
        var movementType = this.getMovementType(),
            mappings = {}

        switch (movementType) {
            case 'swim':
                for (var t in Terrain.toArray()) {
                    var moveCost = 0, tmp

                    for (var i in  army.swim) {
                        tmp = Terrain.get(t)[movementType]
                        if (tmp > moveCost) {
                            moveCost = tmp
                        }
                    }

                    mappings[t] = moveCost
                }
                break
            case 'fly':
                for (var t in Terrain.toArray()) {
                    var moveCost = 0, tmp
                    for (var i in  army.fly) {
                        tmp = Terrain.get(t)[movementType]
                        if (tmp > moveCost) {
                            moveCost = tmp
                        }
                    }

                    mappings[t] = moveCost
                }
                break
            case 'walk':
                for (var t in Terrain.toArray()) {
                    var moveCost = 0, tmp

                    for (var i in  army.walk) {
                        var soldier = army.walk[i],
                            unit = Units.get(soldier.unitId)
                        if (isSet(unit[t])) {
                            tmp = unit[t]
                        } else {
                            tmp = Terrain.get(t)[movementType]
                        }
                        if (tmp > moveCost) {
                            moveCost = tmp
                        }
                    }
                    for (var i in  army.fly) {
                        tmp = Terrain.get(t)[movementType]
                        if (tmp > moveCost) {
                            moveCost = tmp
                        }
                    }
                    for (var i in  army.heroes) {
                        tmp = Terrain.get(t)[movementType]
                        if (tmp > moveCost) {
                            moveCost = tmp
                        }
                    }

                    mappings[t] = moveCost
                }
                break
            default:
                alert('Kurde balanse ale fajanse')
        }

        return mappings
    }
    this.getBackgroundColor = function () {
        return bgColor
    }
    this.countLife = function () {
        var remainingLife = 0,
            defaultLife = 0
        for (var i in army.heroes) {
            remainingLife += army.heroes[i].remainingLife
            defaultLife += 10
        }
        for (var i in army.fly) {
            remainingLife += army.fly[i].remainingLife
            defaultLife += Units.get(army.fly[i].unitId).l
        }
        for (var i in army.swim) {
            remainingLife += army.swim[i].remainingLife
            defaultLife += Units.get(army.swim[i].unitId).l
        }
        for (var i in army.walk) {
            remainingLife += army.walk[i].remainingLife
            defaultLife += Units.get(army.walk[i].unitId).l
        }

        return remainingLife / defaultLife
    }
    this.countUpkeep = function () {
        var upkeep = 0

        for (var i in army.walk) {
            upkeep += Units.get(army.walk[i].unitId).cost
        }
        for (var i in army.swim) {
            upkeep += Units.get(army.swim[i].unitId).cost
        }
        for (var i in army.fly) {
            upkeep += Units.get(army.fly[i].unitId).cost
        }

        return upkeep
    }

    army.mesh = GameModels.addArmy(army.x, army.y, bgColor, Unit.countNumberOfUnits(army), this.getModelName(), this.canSwim(), this.countLife())

    Fields.get(army.x, army.y).addArmyId(army.id, color)
}
