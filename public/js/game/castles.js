var Castles = new function () {
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
        return castles[castleId]
    }
    this.count = function () {
        var i = 0
        for (var castleId in castles) {
            i++
        }
        return i
    }
}