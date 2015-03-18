var Tower = function (tower, bgColor) {
    var mesh = Three.addTower(tower.x, tower.y, bgColor)

    this.update = function (bgColor) {
        console.log(mesh.children)
        mesh.children[0].material.color.set(bgColor)
    }
    this.getX = function () {
        return tower.x
    }
    this.getY = function () {
        return tower.y
    }
}
