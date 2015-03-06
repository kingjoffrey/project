var Tower = function (tower, bgColor) {
    var meshId = Three.addTower(tower.x, tower.y, bgColor)

    this.update = function (bgColor) {
        Three.getScene().getObjectById(meshId).material.color.set(bgColor)
    }
    this.getX = function () {
        return tower.x
    }
    this.getY = function () {
        return tower.y
    }
}
