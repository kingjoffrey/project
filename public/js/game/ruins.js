var Ruins = new function () {
    var ruins = {}
    this.init = function (ruins) {
        for (var ruinId in ruins) {
            this.add(ruinId, new Ruin(ruins[ruinId]))
        }
    }
    this.add = function (ruinId, ruin) {
        ruins[ruinId] = ruin
        Fields.get(ruin.getX(), ruin.getY()).setRuinId(ruinId)
    }
    this.get = function (ruinId) {
        return ruins[ruinId]
    }
}
