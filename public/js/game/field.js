var Field = function (field) {
    var empty = field.empty

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
    this.getArmies = function () {
        return field.armies
    }
    this.hasArmies = function () {
        if (!field.armies) {
            return false
        }
        if (field.armies.constructor === Array) {
            return false
        }
        if (field.armies.constructor === Object) {
            return true
        }
    }
    this.getTowerColor = function () {
        return field.towerColor
    }
    this.getCastleColor = function () {
        return field.castleColor
    }
    this.getType = function () {
        if (field.temporaryType) {
            return field.temporaryType
        } else {
            return field.type
        }
    }
    this.setTemporaryType = function (value) {
        field.temporaryType = value
    }
    this.setCastleColor = function (color) {
        field.castleColor = color
    }
}