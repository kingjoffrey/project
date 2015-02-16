var Castles = function () {
    var castles = {}, bgColor, miniMapColor, textColor
    this.init = function (castles, bgC, miniMapC, textC) {
        bgColor = bgC, miniMapColor = miniMapC, textColor = textC
        for (var castleId in castles) {
            this.add(castleId, castles[castleId])
        }
    }
    this.add = function (castleId, castle) {
        if (castle instanceof Castle) {
            console.log(castle)
            castles[castleId] = castle
            castle.update(bgColor, miniMapColor, textColor)
        } else {
            castles[castleId] = new Castle(castle, bgColor, miniMapColor, textColor)
        }
    }
    this.remove = function (castleId) {
        delete castles[castleId]
    }
    /**
     *
     * @param castleId
     * @returns Castle
     */
    this.get = function (castleId) {
        if (this.has(castleId)) {
            return castles[castleId]
        } else {
            console.log('No castle in castles with id=' + castleId)
        }
    }
    this.has = function (castleId) {
        return isSet(castles[castleId])
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