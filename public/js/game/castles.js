var Castles = function () {
    var castles = {}
    this.init = function (castles, bgColor, miniMapColor, textColor) {
        for (var castleId in castles) {
            this.add(castleId, castles[castleId], bgColor, miniMapColor, textColor)
        }
    }
    this.add = function (castleId, castle, bgColor, miniMapColor, textColor) {
        castles[castleId] = new Castle(castle, bgColor, miniMapColor, textColor)
    }
    this.get = function (castleId) {
        if (isSet(castles[castleId])) {
            return castles[castleId]
        } else {
            console.log('No castle in castles with id=' + castleId)
        }
    }
    this.count = function () {
        var i = 0
        for (var castleId in castles) {
            i++
        }
        return i
    }
    this.attachEventsControls = function () {
        for (var castleId in castles) {
            EventsControls.attach(Three.getScene().getObjectById(this.get(castleId).getMeshId()))
        }
    }
}