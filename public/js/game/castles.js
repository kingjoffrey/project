var Castles = new function () {
    var castles = {}
    this.init = function (castles) {
        for (var castleId in castles) {
            this.add(castleId, castles[castleId])
        }
    }
    this.add = function (castleId, castle) {
        castles[castleId] = new Castle(castle)
    }
    this.get = function (castleId) {
        return castles[castleId]
    }
}