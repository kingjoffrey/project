var Field = function (field) {
    var temporaryType = field.temporaryType,
        empty = field.empty

    this.getRuinId = function () {
        return field.ruinId
    }
    this.getTowerId = function () {
        return field.towerId
    }
    this.getCastleId = function () {
        return field.castleId
    }
    this.removeArmyId = function (armyId) {
        delete field.armies[armyId]
    }
    this.addArmyId = function (armyId, color) {
        field.armies[armyId] = color
    }
    this.getTowerColor = function () {
        return field.towerColor
    }
    this.getCastleColor = function () {
        return field.castleColor
    }
    this.getType = function () {
        return field.type
    }
}