var Field = function (field) {
    var type = field.type,
        temporaryType = field.temporaryType,
        castleColor = field.castleColor,
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
        console.log(field.armies)
    }
    this.addArmyId = function (armyId, color) {
        field.armies[armyId] = color
        console.log(field.armies)
    }
    this.getTowerColor = function () {
        return field.towerColor
    }
}