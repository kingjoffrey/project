var Field = function (field) {
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
    this.getArmies = function () {
        return field.armies
    }
    this.hasArmies = function () {
        for(var armyId in field.armies){
            return armyId
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