var Towers = function () {
    var towers = {}
    this.init = function (towers, bgColor) {
        for (var towerId in towers) {
            this.add(towerId, towers[towerId], bgColor)
        }
    }
    this.add = function (towerId, tower, bgColor) {
        towers[towerId] = new Tower(tower, bgColor)
    }
    this.get = function () {
        return towers[towerId]
    }
}
