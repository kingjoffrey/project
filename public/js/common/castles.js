var Castles = function () {
    var castles,
        bgColor,
        miniMapColor,
        textColor,
        color

    this.init = function (bgC, miniMapC, textC, c) {
        castles = {}

        bgColor = bgC
        miniMapColor = miniMapC
        textColor = textC
        color = c
    }
    this.add = function (castleId, castle, isCapital) {
        var setCastleId = false
        if (!(castle instanceof Castle)) {
            castle = new Castle(castle, bgColor, isCapital)
            setCastleId = true
        }

        for (var x = castle.getX(); x <= castle.getX() + 1; x++) {
            for (var y = castle.getY(); y <= castle.getY() + 1; y++) {
                var field = Fields.get(x, y)
                field.setCastleColor(color)
                if (setCastleId) {
                    field.setCastleId(castleId)
                }
            }
        }

        castles[castleId] = castle

        // castle.update(bgColor)
    }
    this.raze = function (castleId) {
        var castle = this.get(castleId)

        for (var x = castle.getX(); x <= castle.getX() + 1; x++) {
            for (var y = castle.getY(); y <= castle.getY() + 1; y++) {
                var field = Fields.get(x, y)
                field.setCastleColor(0)
                field.setCastleId(0)
            }
        }
        GameScene.remove(castle.getMesh())
        delete castles[castleId]
    }
    this.delete = function (castleId) {
        delete castles[castleId]
    }
    this.clear = function (castleId) {
        GameScene.remove(castles[castleId].getMesh())
        delete castles[castleId]
    }
    /**
     *
     * @param castleId
     * @returns Castle
     */
    this.get = function (castleId) {
        return castles[castleId]
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
    this.toArray = function () {
        return castles
    }
    this.getFirsCastleId = function () {
        for (var castleId in castles) {
            return castleId
        }
    }
}