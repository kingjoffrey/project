var Towers = function () {
    var towers = {}, bgColor, color
    this.init = function (towers, bgC, c) {
        bgColor = bgC
        color = c
        for (var towerId in towers) {
            this.add(towerId, towers[towerId])
        }
    }
    this.add = function (towerId, tower) {
        if (tower instanceof Tower) {
            towers[towerId] = tower
            tower.update(bgColor)
            Fields.get(tower.getX(), tower.getY()).setTowerColor(color)
        } else {
            towers[towerId] = new Tower(tower, bgColor)
            Fields.get(tower.x, tower.y).setTowerColor(color)
            Fields.get(tower.x, tower.y).setTowerId(towerId)
        }

    }
    this.get = function (towerId) {
        return towers[towerId]
    }
    this.remove = function (towerId) {
        Scene.remove(towers[towerId].getMesh())
        delete towers[towerId]
    }
    this.count = function () {
        var i = 0
        for (var towerId in towers) {
            i++
        }
        return i
    }
}
