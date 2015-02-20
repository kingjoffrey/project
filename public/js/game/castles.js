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
    //this.attachPicker = function () {
    //    for (var castleId in castles) {
    //        Picker.attach(Three.getScene().getObjectById(this.get(castleId).getMeshId()))
    //    }
    //}
    this.getRelocatedProduction = function (castleId) {
        var relocatedProduction = []
        for (var id in castles) {
            var castle = this.get(id)
            if (castleId == castle.getCastleId) {
                continue
            }
            if (castleId == castle.getRelocationCastleId()) {
                relocatedProduction.push(castle.getCastleId())
            }
        }
        return relocatedProduction
    }

}