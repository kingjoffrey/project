var Tower = function (tower, bgColor) {
    var meshId = Three.addTower(tower.x, tower.y, bgColor)
    this.update = function (bgColor) {
        Three.getScene().getObjectById(meshId).material.color.set(bgColor)
    }
}
