var Castles = new function () {
    var castles = {}
    this.init = function (castles, bgColor) {
        for (var castleId in castles) {
            this.add(castleId, castles[castleId], bgColor)
        }
    }
    this.add = function (castleId, castle, bgColor) {
        castles[castleId] = new Castle(castle, bgColor)
    }
    this.get = function (castleId) {
        return castles[castleId]
    }
}