var Field = function (field) {
    var empty = field.empty,
        ruinId = field.ruinId

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
        if (field.temporaryType) {
            console.log(field.temporaryType)
            //    field.temporaryType
        }
        //else {
        return field.type
        //}
    }
    this.setTemporaryType = function (value) {
        field.temporaryType = value
    }
    this.getTemporaryType = function () {
        return field.temporaryType
    }
}