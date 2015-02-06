var Field = function (field) {
    var type = field.type,
        temporaryType = field.temporaryType,
        armies = field.armies,
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
}