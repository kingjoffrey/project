var Castles = function () {
    var castles = {},
        bgColor,
        miniMapColor,
        textColor,
        color

    this.init = function (castles, bgC, miniMapC, textC, c) {

        bgColor = bgC
        miniMapColor = miniMapC
        textColor = textC
        color = c

        for (var castleId in castles) {
            this.add(castleId, castles[castleId])
        }
    }
    this.add = function (castleId, castle) {
        if (castle instanceof Castle) {
            if (Me.sameTeam(color)) {
                var type = null
            } else {
                type = 'e'
            }
            for (var x = castle.getX(); x <= castle.getX() + 1; x++) {
                for (var y = castle.getY(); y <= castle.getY() + 1; y++) {
                    var field = Fields.get(x, y)
                    field.setCastleColor(color)
                }
            }
            castles[castleId] = castle
            castle.update(bgColor, miniMapColor, textColor)
        } else {
            castles[castleId] = new Castle(castle, bgColor)
            for (var x = castle.x; x <= castle.x + 1; x++) {
                for (var y = castle.y; y <= castle.y + 1; y++) {
                    var field = Fields.get(x, y)
                    field.setCastleColor(color)
                    field.setCastleId(castleId)
                }
            }
        }
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
    this.toArray = function () {
        return castles
    }
    this.getFirsCastleId = function () {
        for (var castleId in castles) {
            return castleId
        }
    }
}