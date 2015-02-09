var Towers = function () {
    var towers = {}, bgColor
    this.init = function (towers, bgC) {
        bgColor = bgC
        for (var towerId in towers) {
            this.add(towerId, towers[towerId])
        }
    }
    this.add = function (towerId, tower) {
        towers[towerId] = new Tower(tower, bgColor)
    }
    this.get = function (towerId) {
        return towers[towerId]
    }
    this.remove = function (towerId) {
        delete towers[towerId]
    }
}
