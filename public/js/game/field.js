var Field = function (field) {
    var type = field.type,
        temporaryType = field.temporaryType,
        castleColor = field.castleColor,
        towerColor = field.towerColor,
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
    this.removeArmyId = function () {
        console.log(field.armies)
    }
}