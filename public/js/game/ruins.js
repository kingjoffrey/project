var Ruins = new function () {
    var ruins = {}
    this.init = function (ruins) {
        for (var ruinId in ruins) {
            ruins[ruinId] = Ruin.create(ruinId)
        }
    }
}
