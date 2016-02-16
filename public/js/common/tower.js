var Tower = function (tower, bgColor) {
    var mesh = Models.addTower(tower.x, tower.y, bgColor)

    this.update = function (bgColor) {
        mesh.children[0].material.color.set(bgColor)
    }
    this.getX = function () {
        return tower.x
    }
    this.getY = function () {
        return tower.y
    }
    this.getMesh = function () {
        return mesh
    }
}
